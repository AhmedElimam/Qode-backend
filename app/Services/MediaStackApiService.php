<?php

namespace App\Services;

use App\Enums\NewsCategory;
use App\Enums\NewsSource;
use App\Models\Article;
use App\Repositories\ArticleRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MediaStackApiService
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.mediastack.key');
        $this->baseUrl = NewsSource::MEDIASTACK->getApiEndpoint();
    }

    public function fetchArticles(?string $category = null, int $limit = 100): array
    {
        try {
            $params = [
                'access_key' => $this->apiKey,
                'limit' => $limit,
                'sort' => 'published_desc',
            ];

            if ($category) {
                $params['categories'] = $this->mapCategory($category);
            }

            $response = Http::get("{$this->baseUrl}/news", $params);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['data']) && is_array($data['data'])) {
                    return $this->processArticles($data['data']);
                }
            }

            Log::error('MediaStack API error', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('MediaStack API exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    public function searchArticles(string $keyword, ?string $category = null, int $limit = 100): array
    {
        try {
            $params = [
                'access_key' => $this->apiKey,
                'keywords' => $keyword,
                'limit' => $limit,
                'sort' => 'published_desc',
            ];

            if ($category) {
                $params['categories'] = $this->mapCategory($category);
            }

            $response = Http::get("{$this->baseUrl}/news", $params);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['data']) && is_array($data['data'])) {
                    return $this->processArticles($data['data']);
                }
            }

            Log::error('MediaStack search API error', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('MediaStack search API exception', [
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
            try {
                $processedArticle = [
                    'title' => $article['title'] ?? '',
                    'description' => $article['description'] ?? '',
                    'content' => $article['content'] ?? '',
                    'url' => $article['url'] ?? '',
                    'image_url' => $article['image'] ?? null,
                    'author' => $article['author'] ?? null,
                    'source' => NewsSource::MEDIASTACK,
                    'category' => $this->mapCategoryToEnum($article['category'] ?? 'general'),
                    'published_at' => $article['published_at'] ?? now(),
                    'external_id' => $article['url'] ?? uniqid('mediastack_'),
                    'metadata' => [
                        'source_name' => $article['source'] ?? 'MediaStack',
                        'language' => $article['language'] ?? 'en',
                        'country' => $article['country'] ?? null,
                        'api_source' => 'mediastack',
                    ],
                ];

                $processedArticles[] = $processedArticle;
            } catch (\Exception $e) {
                Log::error('Error processing MediaStack article', [
                    'article' => $article,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $processedArticles;
    }

    private function mapCategory(string $category): string
    {
        return match($category) {
            'business' => 'business',
            'technology' => 'technology',
            'sports' => 'sports',
            'entertainment' => 'entertainment',
            'health' => 'health',
            'science' => 'science',
            'general' => 'general',
            default => 'general',
        };
    }

    private function mapCategoryToEnum(string $category): NewsCategory
    {
        return match($category) {
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
