<?php

namespace App\Repositories;

use App\Models\Article;
use App\Enums\NewsSource;
use App\Enums\NewsCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ArticleRepository
{
    public function __construct(
        private Article $model
    ) {}

    public function findById(int $id): ?Article
    {
        return $this->model->find($id);
    }

    public function findByExternalId(string $externalId): ?Article
    {
        return $this->model->where('external_id', $externalId)->first();
    }

    public function search(
        ?string $keyword = null,
        ?NewsSource $source = null,
        ?NewsCategory $category = null,
        ?string $startDate = null,
        ?string $endDate = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        $query = $this->model->query();

        if ($keyword) {
            $query->search($keyword);
        }

        if ($source) {
            $query->bySource($source);
        }

        if ($category) {
            $query->byCategory($category);
        }

        if ($startDate && $endDate) {
            $query->byDateRange($startDate, $endDate);
        }

        return $query->orderBy('published_at', 'desc')->paginate($perPage);
    }

    public function getPersonalizedFeed(
        array $sources,
        array $categories,
        int $perPage = 20
    ): LengthAwarePaginator {
        $query = $this->model->query();
        
        if (!empty($sources) && !empty($categories)) {
            $query->where(function($q) use ($sources, $categories) {
                $q->whereIn('source', $sources)
                  ->orWhereIn('category', $categories);
            });
        } elseif (!empty($sources)) {
            $query->whereIn('source', $sources);
        } elseif (!empty($categories)) {
            $query->whereIn('category', $categories);
        }
        
        return $query->orderBy('published_at', 'desc')->paginate($perPage);
    }

    public function create(array $data): Article
    {
        return $this->model->create($data);
    }

    public function update(Article $article, array $data): bool
    {
        return $article->update($data);
    }

    public function delete(Article $article): bool
    {
        return $article->delete();
    }

    public function getLatestArticles(int $limit = 10): Collection
    {
        return $this->model->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getArticlesBySource(NewsSource $source, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model->bySource($source)
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);
    }

    public function getArticlesByCategory(NewsCategory $category, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model->byCategory($category)
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);
    }
}
