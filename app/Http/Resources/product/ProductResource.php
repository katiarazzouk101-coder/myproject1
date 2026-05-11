<?php

namespace App\Http\Resources\product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'name' => $this->name,
            'detail' => $this->detail,
            'price' => $this->price,
            'category' => $this->category,
            'likes_count' => $this->likes()->count(),
            'is_liked'    => $this->likes->contains('user_id', auth()->id()),
            'average_rating' => round($this->ratings()->avg('rating'), 1),
            'user_rating' => auth()->check()
            ? optional($this->ratings->firstWhere('id', auth()->id()))->pivot?->rating
            : null,
            'images' => $this->images->map(fn($img) => asset('storage/' . $img->path)),
            'created_at' => $this->created_at->format('d/m/Y'),
            'updated_at' => $this->updated_at->format('d/m/Y'),
            'related_product' => ProductListResource::collection($this->getRelatedProducts()),
        ];
    }
}
