<?php

namespace App\Services;

use App\Enums\NewsSource;
use App\Enums\NewsCategory;
use App\Models\Article;
use App\Repositories\ArticleRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsApiService
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private string $apiKey
    ) {}

    public function fetchArticles(
        ?string $keyword = null,
        ?NewsCategory $category = null,
        ?string $language = 'en',
        ?string $country = 'us',
        int $pageSize = 100
    ): array {
        try {
            $params = [
                'apiKey' => $this->apiKey,
                'pageSize' => $pageSize,
                'language' => $language,
                'sortBy' => 'publishedAt',
            ];

            if ($keyword) {
                $params['q'] = $keyword;
            }

            if ($category) {
                $params['category'] = $category->value;
            }

            if ($country) {
                $params['country'] = $country;
            }

            $response = Http::get('https://newsapi.org/v2/top-headlines', $params);

            if ($response->successful()) {
                $data = $response->json();
                return $this->processArticles($data['articles'] ?? []);
            }

            Log::error('NewsAPI request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('NewsAPI service error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    public function searchArticles(
        string $keyword,
        ?string $language = 'en',
        ?string $sortBy = 'publishedAt',
        int $pageSize = 100
    ): array {
        try {
            $params = [
                'apiKey' => $this->apiKey,
                'q' => $keyword,
                'pageSize' => $pageSize,
                'language' => $language,
                'sortBy' => $sortBy,
            ];

            $response = Http::get('https://newsapi.org/v2/everything', $params);

            if ($response->successful()) {
                $data = $response->json();
                return $this->processArticles($data['articles'] ?? []);
            }

            Log::error('NewsAPI search request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('NewsAPI search service error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    private function processArticles(array $articles): array
    {
        $processedArticles = [];

        foreach ($articles as $article) {
            $processedArticle = $this->mapArticleData($article);
            
            if ($processedArticle) {
                $existingArticle = $this->articleRepository->findByExternalId($processedArticle['external_id']);
                
                if (!$existingArticle) {
                    $this->articleRepository->create($processedArticle);
                }
                
                $processedArticles[] = $processedArticle;
            }
        }

        return $processedArticles;
    }

    private function mapArticleData(array $article): ?array
    {
        if (empty($article['title']) || empty($article['url'])) {
            return null;
        }

        $category = $this->mapCategory($article['category'] ?? 'general');
        $externalId = md5($article['url'] . $article['publishedAt']);

        return [
            'title' => $article['title'],
            'description' => $article['description'] ?? null,
            'content' => $article['content'] ?? null,
            'url' => $article['url'],
            'image_url' => $article['urlToImage'] ?? null,
            'author' => $article['author'] ?? null,
            'source' => NewsSource::NEWS_API,
            'category' => $category,
            'published_at' => $article['publishedAt'],
            'external_id' => $externalId,
            'metadata' => [
                'source_name' => $article['source']['name'] ?? null,
                'source_id' => $article['source']['id'] ?? null,
            ],
        ];
    }

    private function mapCategory(string $apiCategory): NewsCategory
    {
        return match(strtolower($apiCategory)) {
            'business' => NewsCategory::BUSINESS,
            'technology' => NewsCategory::TECHNOLOGY,
            'sports' => NewsCategory::SPORTS,
            'entertainment' => NewsCategory::ENTERTAINMENT,
            'health' => NewsCategory::HEALTH,
            'science' => NewsCategory::SCIENCE,
            'politics' => NewsCategory::POLITICS,
            'world' => NewsCategory::WORLD,
            'national' => NewsCategory::NATIONAL,
            'local' => NewsCategory::LOCAL,
            'opinion' => NewsCategory::OPINION,
            'arts' => NewsCategory::ARTS,
            'food' => NewsCategory::FOOD,
            'travel' => NewsCategory::TRAVEL,
            'education' => NewsCategory::EDUCATION,
            default => NewsCategory::WORLD,
        };
    }
}
