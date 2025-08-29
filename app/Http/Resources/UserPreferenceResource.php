<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPreferenceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'language' => $this->language,
            'country' => $this->country,
            'articles_per_page' => $this->articles_per_page,
            'notifications_enabled' => $this->notifications_enabled,
            'settings' => $this->settings,
            'sources' => $this->whenLoaded('user', function () {
                return $this->user->sources()->where('is_active', true)->pluck('source');
            }),
            'categories' => $this->whenLoaded('user', function () {
                return $this->user->categories()->where('is_active', true)->pluck('category');
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
