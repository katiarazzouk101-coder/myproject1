<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Product;
use Validator;
use App\Http\Resources\product\ProductListResource;
use App\Http\Resources\product\ProductResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $products = Product::with(['category', 'likes','ratings'])
        ->when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%")
                         ->orWhere('detail', 'like', "%{$search}%");
        })->paginate(10);
    
        return $this->sendResponse(ProductListResource::collection($products), 'Products retrieved successfully.', true, $products);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
   
        $validator = Validator::make($input, [
            'name' => 'required',
            'detail' => 'required',
            'price' => 'required',
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'images.*' => 'image|max:2048',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
   
        $product = auth()->user()->products()->create($input);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $product->images()->create(['path' => $path]);
            }
        }
        return $this->sendResponse(new ProductResource($product), 'Product created successfully.');
    } 
   
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::find($id);
  
        if (is_null($product)) {
            return $this->sendError('Product not found.');
        }
   
        return $this->sendResponse(new ProductResource($product), 'Product retrieved successfully.');
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'name' => 'required',
            'detail' => 'required',
            'price' => 'required',
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'images.*' => 'nullable|image|max:2048',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
   
        $product->update($input);

        if ($request->hasFile('images')) {
            foreach ($product->images as $oldImage) {
                Storage::disk('public')->delete($oldImage->path);
                $oldImage->delete(); 
            }
        
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $product->images()->create(['path' => $path]);
            }
        }
   
        return $this->sendResponse(new ProductResource($product), 'Product updated successfully.');
    }
   
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $product->delete();
   
        return $this->sendResponse([], 'Product deleted successfully.');
    }

    public function like(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $like = $product->likes()->firstOrCreate([
            'user_id' => auth()->id(),
        ]);

        return response()->json(['liked' => true]);
    }

    public function unlike(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $product->likes()->where('user_id', auth()->id())->delete();

        return response()->json(['liked' => false]);
    }

    public function rate(Request $request, Product $product)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'rating' => 'required|numeric|in:0,0.5,1,1.5,2,2.5,3,3.5,4,4.5,5',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        $user = auth()->user();

        // Update or create rating
        $user->ratedProducts()->syncWithoutDetaching([
            $product->id => ['rating' => $input['rating']]
        ]);

        return response()->json(['message' => 'Product rated successfully.']);
    }

    public function likedProducts(Request $request)
    {
        $user = $request->user();
        
        $likedProducts = $user->likedProducts()->with('category')->get();
        return $this->sendResponse(ProductListResource::collection($likedProducts), 'Products retrieved successfully.');
    }
}