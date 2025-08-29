<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['external_id'] ?? uniqid(),
            'title' => $this['title'] ?? '',
            'description' => $this['description'] ?? '',
            'content' => $this['content'] ?? '',
            'url' => $this['url'] ?? '',
            'image_url' => $this['image_url'] ?? null,
            'author' => $this['author'] ?? null,
            'source' => [
                'value' => $this['source']->value ?? $this['source'],
                'display_name' => $this['source']->getDisplayName() ?? $this['source'],
            ],
            'category' => [
                'value' => $this['category']->value ?? $this['category'],
                'display_name' => $this['category']->getDisplayName() ?? $this['category'],
            ],
            'published_at' => $this['published_at'] ?? now(),
            'metadata' => $this['metadata'] ?? [],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
