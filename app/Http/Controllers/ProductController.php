<?php

namespace App\Http\Controllers;

use App\Product;
use App\ProductImages;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $products = Product::orderBy('name', 'asc');
        
        // check if request has a filter data
        if (!empty($request->filters)) {
            $filters = json_decode($request->filters);
            if (!empty($filters->category_id)) {
                $products = $products->where('category_id', $filters->category_id);
            }
            // SEARCH
            if (!empty($filters->search)) {
                $search = $filters->search;
                $products = $products->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            }
        }

        return $products->get()->load('category');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($request, $product)
    {
        try 
        {
            $product->name = $request->name;
            $product->category_id = $request->category_id;
            $product->description = $request->description;
            $product->date_time = $request->date_time;
            $product->save();

            if($request->images) {
                // delete image
                ProductImages::where('product_id', $product->id)->forceDelete();
                // create product images
                foreach ($request->images as $image) {
                    $url = $this->uploadImageBase64($request->images, 'product_images');
                    $product_image = new ProductImages();
                    $product_image->product_id = $product->id;
                    $product_image->image_url = $url;
                    $product_image->save();
                }
            }
            return 'success';

        } catch (\Exception $e) {
            Log::error($e);
            return ['error', 'Internal error'];
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $product = new  Product();

        return $this->create($request, $product);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return $product;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        return $product;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        return $this->create($request, $product);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return 'success';
    }
    
    function uploadImageBase64($content, $path, $name = null)
    {
        $image = Image::make(base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $content)));
        $ext = explode("/", $image->mime)[1];
        //$name = $name . '_' . time();
        $name = empty($name) ? time() . ".$ext" : $name;
        Storage::put("public/$path/$name", $image->stream());

        return "/storage/$path/$name";
    }
}