<?php
  
namespace App\Http\Resources\product;
  
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
  
class ProductListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array
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
            'is_liked'    => auth()->check() 
            ? $this->likes()->where('user_id', auth()->id())->exists() 
            : false,
            'average_rating' => round($this->ratings()->avg('rating'), 1),
            'user_rating' => auth()->check()
            ? optional($this->ratings->firstWhere('id', auth()->id()))->pivot?->rating
            : null,
            'images' => $this->images->map(fn($img) => asset('storage/' . $img->path)),
        ];
    }
}