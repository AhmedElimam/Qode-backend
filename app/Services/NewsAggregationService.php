<?php

namespace App\Services;

use App\Enums\NewsCategory;
use App\Enums\NewsSource;
use App\Models\User;
use App\Repositories\ArticleRepository;
use Illuminate\Support\Facades\Log;

class NewsAggregationService
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private GuardianApiService $guardianApiService,
        private NewYorkTimesApiService $nyTimesApiService,
        private MediaStackApiService $mediaStackApiService
    ) {}

    public function fetchArticlesFromAllSources(): array
    {
        $allArticles = [];

        try {
            $sources = [
                NewsSource::NEWS_API => $this->newsApiService,
                NewsSource::THE_GUARDIAN => $this->guardianApiService,
                NewsSource::NEW_YORK_TIMES => $this->nyTimesApiService,
                NewsSource::MEDIASTACK => $this->mediaStackApiService,
            ];

            foreach ($sources as $source => $service) {
                try {
                    Log::info("Fetching articles from {$source->value}");
                    $articles = $service->fetchArticles();
                    $allArticles = array_merge($allArticles, $articles);
                    Log::info("Fetched " . count($articles) . " articles from {$source->value}");
                } catch (\Exception $e) {
                    Log::error("Error fetching articles from {$source->value}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $this->articleRepository->bulkCreate($allArticles);

            return $allArticles;
        } catch (\Exception $e) {
            Log::error('Error in fetchArticlesFromAllSources', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    public function searchArticles(
        string $keyword,
        ?NewsSource $source = null,
        ?NewsCategory $category = null,
        ?string $startDate = null,
        ?string $endDate = null,
        int $perPage = 20
    ): array {
        try {
            if ($source) {
                return $this->searchFromSpecificSource($source, $keyword, $category, $perPage);
            }

            $articles = [];
            $sources = [
                NewsSource::THE_GUARDIAN->value => $this->guardianApiService,
                NewsSource::NEW_YORK_TIMES->value => $this->nyTimesApiService,
                NewsSource::MEDIASTACK->value => $this->mediaStackApiService,
            ];

            foreach ($sources as $sourceKey => $service) {
                try {
                    $sourceArticles = $service->searchArticles($keyword, $category?->value, $perPage);
                    $articles = array_merge($articles, $sourceArticles);
                } catch (\Exception $e) {
                    Log::error("Error searching articles from " . $sourceKey, [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return $articles;
        } catch (\Exception $e) {
            Log::error('Error in searchArticles', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    private function searchFromSpecificSource(
        NewsSource $source,
        string $keyword,
        ?NewsCategory $category,
        int $perPage
    ): array {
        return match($source) {
            NewsSource::THE_GUARDIAN => $this->guardianApiService->searchArticles($keyword, $category?->value, $perPage),
            NewsSource::NEW_YORK_TIMES => $this->nyTimesApiService->searchArticles($keyword, $category?->value, $perPage),
            NewsSource::MEDIASTACK => $this->mediaStackApiService->searchArticles($keyword, $category?->value, $perPage),
            default => [],
        };
    }

    public function getPersonalizedFeed(User $user, int $perPage = 20): \Illuminate\Pagination\LengthAwarePaginator
    {
        try {
            $userSources = $user->sources()->where('is_active', true)->pluck('source')->toArray();
            $userCategories = $user->categories()->where('is_active', true)->pluck('category')->toArray();

            Log::info('User preferences for personalized feed', [
                'user_id' => $user->id,
                'sources' => $userSources,
                'categories' => $userCategories
            ]);

            if (empty($userSources) && empty($userCategories)) {
                Log::info('No user preferences found, returning latest articles');
                return $this->articleRepository->search(null, null, null, null, null, $perPage);
            }

            return $this->articleRepository->getPersonalizedFeed($userSources, $userCategories, $perPage);
        } catch (\Exception $e) {
            Log::error('Error in getPersonalizedFeed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }
    }

    public function refreshArticles(): array
    {
        return $this->fetchArticlesFromAllSources();
    }
}
