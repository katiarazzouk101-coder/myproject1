<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\category\CategoryListResource;
use App\Http\Resources\category\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;

class CategoryController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $category = Category::all();
    
        return $this->sendResponse(CategoryListResource::collection($category), 'Category retrieved successfully.');
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
            'image' => 'nullable|image|max:2048',
        ]);
        
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('categories', 'public');
            $input['image'] = $path;
        }

        $category = Category::create($input);
   
        return $this->sendResponse(new CategoryResource($category), 'Category created successfully.');
    } 
   
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $category = Category::with(relations: 'products')->find($id);
  
        if (is_null($category)) {
            return $this->sendError('Category not found.');
        }
   
        return $this->sendResponse(new CategoryResource($category), 'Category retrieved successfully.');
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        $input = $request->all();
   
        $validator = Validator::make($input, [
            'name' => 'required',
            'image' => 'nullable|image|max:2048',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        if ($request->hasFile('image')) {
            // Optionally delete the old image
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
    
            $path = $request->file('image')->store('categories', 'public');
            $input['image'] = $path;
        }
        
        $category->update($input);
   
        return $this->sendResponse(new CategoryResource($category), 'Category updated successfully.');
    }
   
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        $category->delete();
   
        return $this->sendResponse([], 'Category deleted successfully.');
    }
}
