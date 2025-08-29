<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdatePreferenceRequest;
use App\Http\Requests\User\PreferencesRequest;
use App\Http\Requests\User\ToggleSourceRequest;
use App\Http\Requests\User\ToggleCategoryRequest;
use App\Http\Requests\User\SourcesRequest;
use App\Http\Requests\User\CategoriesRequest;
use App\Http\Resources\UserPreferenceResource;
use App\Repositories\UserPreferenceRepository;
use App\Enums\NewsSource;
use App\Enums\NewsCategory;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private UserPreferenceRepository $userPreferenceRepository
    ) {}

    public function preferences(PreferencesRequest $request): JsonResponse
    {
        $user = $request->user();
        $preference = $this->userPreferenceRepository->getOrCreatePreference($user);

        return $this->resourceResponse(
            new UserPreferenceResource($preference),
            'User preferences retrieved successfully'
        );
    }

    public function updatePreferences(UpdatePreferenceRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if (isset($data['sources'])) {
            $sources = array_map(fn($source) => NewsSource::from($source), $data['sources']);
            $this->userPreferenceRepository->updateUserSources($user, $sources);
            unset($data['sources']);
        }

        if (isset($data['categories'])) {
            $categories = array_map(fn($category) => NewsCategory::from($category), $data['categories']);
            $this->userPreferenceRepository->updateUserCategories($user, $categories);
            unset($data['categories']);
        }

        $preference = $this->userPreferenceRepository->updatePreference($user, $data);

        return $this->updatedResponse(
            new UserPreferenceResource($preference),
            'Preferences updated successfully'
        );
    }

    public function toggleSource(ToggleSourceRequest $request, string $source): JsonResponse
    {
        $user = $request->user();
        $newsSource = NewsSource::from($source);
        $isActive = $this->userPreferenceRepository->toggleSource($user, $newsSource);

        return $this->successResponse([
            'source' => $source,
            'is_active' => $isActive,
        ], $isActive ? 'Source enabled' : 'Source disabled');
    }

    public function toggleCategory(ToggleCategoryRequest $request, string $category): JsonResponse
    {
        $user = $request->user();
        $newsCategory = NewsCategory::from($category);
        $isActive = $this->userPreferenceRepository->toggleCategory($user, $newsCategory);

        return $this->successResponse([
            'category' => $category,
            'is_active' => $isActive,
        ], $isActive ? 'Category enabled' : 'Category disabled');
    }

    public function sources(SourcesRequest $request): JsonResponse
    {
        $user = $request->user();
        $sources = $this->userPreferenceRepository->getUserSources($user);

        return $this->successResponse([
            'sources' => $sources,
        ], 'User sources retrieved successfully');
    }

    public function categories(CategoriesRequest $request): JsonResponse
    {
        $user = $request->user();
        $categories = $this->userPreferenceRepository->getUserCategories($user);

        return $this->successResponse([
            'categories' => $categories,
        ], 'User categories retrieved successfully');
    }
}
