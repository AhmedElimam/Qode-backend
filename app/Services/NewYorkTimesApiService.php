<?php

namespace App\Services;

use App\Enums\NewsSource;
use App\Enums\NewsCategory;
use App\Models\Article;
use App\Repositories\ArticleRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewYorkTimesApiService
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private string $apiKey
    ) {}

    public function fetchArticles(
        ?string $keyword = null,
        ?NewsCategory $category = null,
        ?string $section = null,
        int $pageSize = 20
    ): array {
        try {
            $params = [
                'api-key' => $this->apiKey,
                'fl' => 'headline,abstract,web_url,multimedia,byline,pub_date,section_name,subsection_name',
            ];

            if ($keyword) {
                $params['q'] = $keyword;
            }

            if ($category) {
                $params['fq'] = 'news_desk:(' . $this->mapCategoryToNewsDesk($category) . ')';
            }

            if ($section) {
                $params['fq'] = 'section_name:(' . $section . ')';
            }

            $response = Http::get('https://api.nytimes.com/svc/search/v2/articlesearch.json', $params);

            if ($response->successful()) {
                $data = $response->json();
                return $this->processArticles($data['response']['docs'] ?? []);
            }

            Log::error('NYT API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('NYT API service error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    public function searchArticles(
        string $keyword,
        ?string $category = null,
        int $pageSize = 20
    ): array {
        try {
            $params = [
                'api-key' => $this->apiKey,
                'q' => $keyword,
                'fl' => 'headline,abstract,web_url,multimedia,byline,pub_date,section_name,subsection_name',
            ];

            if ($category) {
                $params['fq'] = 'news_desk:(' . $category . ')';
            }

            $response = Http::get('https://api.nytimes.com/svc/search/v2/articlesearch.json', $params);

            if ($response->successful()) {
                $data = $response->json();
                return $this->processArticles($data['response']['docs'] ?? []);
            }

            Log::error('NYT search API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('NYT search API service error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    public function getTopStories(?string $section = 'home'): array
    {
        try {
            $params = [
                'api-key' => $this->apiKey,
            ];

            $response = Http::get("https://api.nytimes.com/svc/topstories/v2/{$section}.json", $params);

            if ($response->successful()) {
                $data = $response->json();
                return $this->processArticles($data['results'] ?? []);
            }

            Log::error('NYT top stories request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('NYT top stories service error', [
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

            $response = Http::get('https://api.nytimes.com/svc/news/v3/content/all/all.json', $params);

            if ($response->successful()) {
                $data = $response->json();
                return $data['results'] ?? [];
            }

            return [];
        } catch (\Exception $e) {
            Log::error('NYT sections request failed', [
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
        try {
            if (empty($article['headline']) || empty($article['web_url'])) {
                return null;
            }

            $title = is_array($article['headline']) ? ($article['headline']['main'] ?? '') : $article['headline'];
            if (empty($title)) {
                return null;
            }

            $category = $this->mapNewsDeskToCategory($article['news_desk'] ?? 'News');
            $externalId = md5($article['web_url'] . $article['pub_date']);

            $imageUrl = null;
            if (!empty($article['multimedia']) && is_array($article['multimedia'])) {
                foreach ($article['multimedia'] as $media) {
                    if (is_array($media) && ($media['type'] ?? '') === 'image' && ($media['subtype'] ?? '') === 'photo') {
                        $imageUrl = 'https://www.nytimes.com/' . ($media['url'] ?? '');
                        break;
                    }
                }
            }

        return [
            'title' => $title,
            'description' => $article['abstract'] ?? null,
            'content' => null,
            'url' => $article['web_url'],
            'image_url' => $imageUrl,
            'author' => is_array($article['byline']) ? ($article['byline']['original'] ?? null) : $article['byline'],
            'source' => NewsSource::NEW_YORK_TIMES,
            'category' => $category,
            'published_at' => $article['pub_date'],
            'external_id' => $externalId,
            'metadata' => [
                'section_name' => $article['section_name'] ?? null,
                'subsection_name' => $article['subsection_name'] ?? null,
                'news_desk' => $article['news_desk'] ?? null,
                'document_type' => $article['document_type'] ?? null,
            ],
        ];
        } catch (\Exception $e) {
            Log::error('Error mapping NYT article data', [
                'article' => $article,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function mapCategoryToNewsDesk(NewsCategory $category): string
    {
        return match($category) {
            NewsCategory::BUSINESS => 'Business',
            NewsCategory::TECHNOLOGY => 'Technology',
            NewsCategory::SPORTS => 'Sports',
            NewsCategory::ENTERTAINMENT => 'Arts',
            NewsCategory::HEALTH => 'Health',
            NewsCategory::SCIENCE => 'Science',
            NewsCategory::POLITICS => 'Politics',
            NewsCategory::WORLD => 'Foreign',
            NewsCategory::NATIONAL => 'National',
            NewsCategory::LOCAL => 'Metro',
            NewsCategory::OPINION => 'Opinion',
            NewsCategory::ARTS => 'Arts',
            NewsCategory::FOOD => 'Food',
            NewsCategory::TRAVEL => 'Travel',
            NewsCategory::EDUCATION => 'Education',
            default => 'News',
        };
    }

    private function mapNewsDeskToCategory(string $newsDesk): NewsCategory
    {
        return match(strtolower($newsDesk)) {
            'business' => NewsCategory::BUSINESS,
            'technology' => NewsCategory::TECHNOLOGY,
            'sports' => NewsCategory::SPORTS,
            'arts' => NewsCategory::ENTERTAINMENT,
            'health' => NewsCategory::HEALTH,
            'science' => NewsCategory::SCIENCE,
            'politics' => NewsCategory::POLITICS,
            'foreign' => NewsCategory::WORLD,
            'national' => NewsCategory::NATIONAL,
            'metro' => NewsCategory::LOCAL,
            'opinion' => NewsCategory::OPINION,
            'food' => NewsCategory::FOOD,
            'travel' => NewsCategory::TRAVEL,
            'education' => NewsCategory::EDUCATION,
            default => NewsCategory::WORLD,
        };
    }
}
