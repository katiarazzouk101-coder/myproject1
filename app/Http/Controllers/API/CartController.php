<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\CartResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Validator;

class CartController extends BaseController
{
    public function addToCart(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'quantity'   => 'required|integer|min:1',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        $user = auth()->user();

        $cart = $user->cart()->firstOrCreate([]);

        $item = $cart->items()->where('product_id', $request->product_id)->first();

        if ($item) {
            $item->increment('quantity', $request->quantity);   
        } else {
            $item = $cart->items()->create([
                'product_id' => $request->product_id,
                'quantity'   => $request->quantity,
            ]);
        }

        return response()->json(['message' => 'Added to cart', 'item' => $item]);
    }

    public function viewCart()
    {
        $cart = auth()->user()->cart()->with('items.product')->first();

        return $this->sendResponse(new CartResource($cart), 'Cart retrieved successfully.');
    }
    public function removeFromCart($productId)
    {
        $cart = auth()->user()->cart;

        $cart->items()->where('product_id', $productId)->delete();

        return response()->json(['message' => 'Removed from cart']);
    }
}
