<?php

namespace App\Services;

use App\Enums\NewsSource;
use App\Enums\NewsCategory;
use App\Models\Article;
use App\Repositories\ArticleRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GuardianApiService
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private string $apiKey
    ) {}

    public function fetchArticles(
        ?string $keyword = null,
        ?NewsCategory $category = null,
        ?string $section = null,
        int $pageSize = 50
    ): array {
        try {
            $params = [
                'api-key' => $this->apiKey,
                'page-size' => $pageSize,
                'show-fields' => 'headline,trailText,bodyText,thumbnail,byline,lastModified',
                'show-tags' => 'contributor,series',
                'order-by' => 'newest',
            ];

            if ($keyword) {
                $params['q'] = $keyword;
            }

            if ($category) {
                $params['section'] = $this->mapCategoryToSection($category);
            }

            if ($section) {
                $params['section'] = $section;
            }

            $response = Http::get('https://content.guardianapis.com/search', $params);

            if ($response->successful()) {
                $data = $response->json();
                return $this->processArticles($data['response']['results'] ?? []);
            }

            Log::error('Guardian API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Guardian API service error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    public function searchArticles(
        string $keyword,
        ?string $category = null,
        int $pageSize = 50
    ): array {
        try {
            $params = [
                'api-key' => $this->apiKey,
                'q' => $keyword,
                'page-size' => $pageSize,
                'show-fields' => 'headline,trailText,bodyText,thumbnail,byline,lastModified',
                'show-tags' => 'contributor,series',
                'order-by' => 'newest',
            ];

            if ($category) {
                $params['section'] = $category;
            }

            $response = Http::get('https://content.guardianapis.com/search', $params);

            if ($response->successful()) {
                $data = $response->json();
                return $this->processArticles($data['response']['results'] ?? []);
            }

            Log::error('Guardian search API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Guardian search API service error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    public function getSections(): array
    {
        try {
            $params = [
                'api-key' => $this->apiKey,
            ];

            $response = Http::get('https://content.guardianapis.com/sections', $params);

            if ($response->successful()) {
                $data = $response->json();
                return $data['response']['results'] ?? [];
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Guardian sections request failed', [
                'message' => $e->getMessage(),
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
        if (empty($article['webTitle']) || empty($article['webUrl'])) {
            return null;
        }

        $category = $this->mapSectionToCategory($article['sectionName'] ?? 'news');
        $externalId = md5($article['webUrl'] . $article['webPublicationDate']);

        return [
            'title' => $article['webTitle'],
            'description' => $article['fields']['trailText'] ?? null,
            'content' => $article['fields']['bodyText'] ?? null,
            'url' => $article['webUrl'],
            'image_url' => $article['fields']['thumbnail'] ?? null,
            'author' => $article['fields']['byline'] ?? null,
            'source' => NewsSource::THE_GUARDIAN,
            'category' => $category,
            'published_at' => $article['webPublicationDate'],
            'external_id' => $externalId,
            'metadata' => [
                'section_name' => $article['sectionName'] ?? null,
                'section_id' => $article['sectionId'] ?? null,
                'pillar_name' => $article['pillarName'] ?? null,
            ],
        ];
    }

    private function mapCategoryToSection(NewsCategory $category): string
    {
        return match($category) {
            NewsCategory::BUSINESS => 'business',
            NewsCategory::TECHNOLOGY => 'technology',
            NewsCategory::SPORTS => 'sport',
            NewsCategory::ENTERTAINMENT => 'culture',
            NewsCategory::HEALTH => 'society',
            NewsCategory::SCIENCE => 'science',
            NewsCategory::POLITICS => 'politics',
            NewsCategory::WORLD => 'world',
            NewsCategory::NATIONAL => 'uk-news',
            NewsCategory::LOCAL => 'uk-news',
            NewsCategory::OPINION => 'commentisfree',
            NewsCategory::ARTS => 'culture',
            NewsCategory::FOOD => 'lifeandstyle',
            NewsCategory::TRAVEL => 'travel',
            NewsCategory::EDUCATION => 'education',
            default => 'news',
        };
    }

    private function mapSectionToCategory(string $section): NewsCategory
    {
        return match(strtolower($section)) {
            'business' => NewsCategory::BUSINESS,
            'technology' => NewsCategory::TECHNOLOGY,
            'sport' => NewsCategory::SPORTS,
            'culture' => NewsCategory::ENTERTAINMENT,
            'society' => NewsCategory::HEALTH,
            'science' => NewsCategory::SCIENCE,
            'politics' => NewsCategory::POLITICS,
            'world' => NewsCategory::WORLD,
            'uk-news' => NewsCategory::NATIONAL,
            'commentisfree' => NewsCategory::OPINION,
            'lifeandstyle' => NewsCategory::FOOD,
            'travel' => NewsCategory::TRAVEL,
            'education' => NewsCategory::EDUCATION,
            default => NewsCategory::WORLD,
        };
    }
}
