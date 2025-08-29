<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Article\SearchRequest;
use App\Http\Requests\Article\IndexRequest;
use App\Http\Requests\Article\PersonalizedRequest;
use App\Http\Requests\Article\BySourceRequest;
use App\Http\Requests\Article\ByCategoryRequest;
use App\Http\Requests\Article\ShowRequest;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\SearchResultResource;
use App\Repositories\ArticleRepository;
use App\Services\NewsAggregationService;
use App\Enums\NewsSource;
use App\Enums\NewsCategory;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ArticleController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private ArticleRepository $articleRepository,
        private NewsAggregationService $newsAggregationService
    ) {}

    public function index(IndexRequest $request): JsonResponse
    {
        $perPage = $request->get('per_page', 20);
        $articles = $this->articleRepository->getLatestArticles($perPage);

        return $this->paginatedResponse(
            ArticleResource::collection($articles),
            'Latest articles retrieved successfully'
        );
    }

    public function search(SearchRequest $request): JsonResponse
    {
        $keyword = $request->get('keyword');
        $source = $request->get('source') ? NewsSource::from($request->get('source')) : null;
        $category = $request->get('category') ? NewsCategory::from($request->get('category')) : null;
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $perPage = $request->get('per_page', 20);

        if (!$keyword && !$source && !$category && !$startDate && !$endDate) {
            return $this->successResponse([
                'data' => [],
                'message' => 'No search criteria provided',
                'count' => 0,
            ]);
        }

        $articles = $this->newsAggregationService->searchArticles(
            $keyword ?? '',
            $source,
            $category,
            $startDate,
            $endDate,
            $perPage
        );

        return $this->successResponse([
            'data' => SearchResultResource::collection($articles),
            'message' => 'Search results retrieved successfully',
            'count' => count($articles),
        ]);
    }

    public function personalized(PersonalizedRequest $request): JsonResponse
    {
        $perPage = $request->get('per_page', 20);
        $articles = $this->newsAggregationService->getPersonalizedFeed($request->user(), $perPage);

        return $this->paginatedResponse(
            ArticleResource::collection($articles),
            'Personalized feed retrieved successfully'
        );
    }

    public function bySource(BySourceRequest $request, string $source): JsonResponse
    {
        $newsSource = NewsSource::from($source);
        $perPage = $request->get('per_page', 20);

        $articles = $this->articleRepository->getArticlesBySource($newsSource, $perPage);

        return $this->paginatedResponse(
            ArticleResource::collection($articles),
            "Articles from {$source} retrieved successfully"
        );
    }

    public function byCategory(ByCategoryRequest $request, string $category): JsonResponse
    {
        $newsCategory = NewsCategory::from($category);
        $perPage = $request->get('per_page', 20);

        $articles = $this->articleRepository->getArticlesByCategory($newsCategory, $perPage);

        return $this->paginatedResponse(
            ArticleResource::collection($articles),
            "Articles in {$category} category retrieved successfully"
        );
    }

    public function show(ShowRequest $request, int $id): JsonResponse
    {
        $article = $this->articleRepository->findById($id);

        if (!$article) {
            return $this->notFoundResponse('Article not found');
        }

        return $this->resourceResponse(
            new ArticleResource($article),
            'Article retrieved successfully'
        );
    }

    public function refresh(): JsonResponse
    {
        $articles = $this->newsAggregationService->refreshArticles();

        return $this->successResponse([
            'count' => count($articles),
        ], 'Articles refreshed successfully');
    }

    public function categories(): JsonResponse
    {
        $categories = NewsCategory::cases();
        $categoryData = array_map(fn($category) => [
            'value' => $category->value,
            'display_name' => $category->getDisplayName()
        ], $categories);

        return $this->successResponse($categoryData, 'Categories retrieved successfully');
    }

    public function sources(): JsonResponse
    {
        $sources = NewsSource::cases();
        $sourceData = array_map(fn($source) => [
            'value' => $source->value,
            'display_name' => $source->getDisplayName()
        ], $sources);

        return $this->successResponse($sourceData, 'Sources retrieved successfully');
    }
}
