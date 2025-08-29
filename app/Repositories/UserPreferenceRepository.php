<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\UserPreference;
use App\Models\UserSource;
use App\Models\UserCategory;
use App\Enums\NewsSource;
use App\Enums\NewsCategory;

class UserPreferenceRepository
{
    public function __construct(
        private UserPreference $preferenceModel,
        private UserSource $sourceModel,
        private UserCategory $categoryModel
    ) {}

    public function getOrCreatePreference(User $user): UserPreference
    {
        $preference = $user->preference ?? $user->preference()->create([
            'language' => 'en',
            'country' => 'us',
            'articles_per_page' => 20,
            'notifications_enabled' => true,
        ]);
        
        $user->load(['sources', 'categories']);
        
        return $preference;
    }

    public function updatePreference(User $user, array $data): UserPreference
    {
        $preference = $this->getOrCreatePreference($user);
        $preference->update($data);
        return $preference;
    }

    public function getUserSources(User $user): array
    {
        return $user->sources()
            ->where('is_active', true)
            ->pluck('source')
            ->toArray();
    }

    public function getUserCategories(User $user): array
    {
        return $user->categories()
            ->where('is_active', true)
            ->pluck('category')
            ->toArray();
    }

    public function updateUserSources(User $user, array $sources): void
    {
        $user->sources()->delete();
        
        foreach ($sources as $source) {
            $user->sources()->create([
                'source' => $source,
                'is_active' => true,
            ]);
        }
    }

    public function updateUserCategories(User $user, array $categories): void
    {
        $user->categories()->delete();
        
        foreach ($categories as $category) {
            $user->categories()->create([
                'category' => $category,
                'is_active' => true,
            ]);
        }
    }

    public function toggleSource(User $user, NewsSource $source): bool
    {
        $userSource = $user->sources()->where('source', $source)->first();
        
        if ($userSource) {
            $userSource->update(['is_active' => !$userSource->is_active]);
            return $userSource->is_active;
        } else {
            $user->sources()->create([
                'source' => $source,
                'is_active' => true,
            ]);
            return true;
        }
    }

    public function toggleCategory(User $user, NewsCategory $category): bool
    {
        $userCategory = $user->categories()->where('category', $category)->first();
        
        if ($userCategory) {
            $userCategory->update(['is_active' => !$userCategory->is_active]);
            return $userCategory->is_active;
        } else {
            $user->categories()->create([
                'category' => $category,
                'is_active' => true,
            ]);
            return true;
        }
    }
}
