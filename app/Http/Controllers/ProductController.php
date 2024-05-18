<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function create(Request $request)
{
    $data = JWT::decode($request->bearerToken(), new Key(env('JWT_SECRET_KEY'), 'HS256'));
    $user = User::find($data->id);

    // User is authenticated, proceed with validation and data creation
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'description' => 'required|string',
        'price' => 'required|integer',
        'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validasi untuk file gambar
        'category_id' => 'required|string|max:255', // Accept category as name
        'expired_at' => 'required|date',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->messages())->setStatusCode(422);
    }

    $validated = $validator->validated();

    // Handle category input (name)
    $categoryName = $validated['category_id'];
    $category = Category::firstOrCreate(['name' => $categoryName]);

    if (!$category) {
        return response()->json(['message' => 'Invalid category'], 422);
    }

    $validated['category_id'] = $category->id;

    // Retrieve user ID and email
    $userId = $user->id;
    $userEmail = $user->email;

    // Ensure user ID is not null before proceeding
    if (!$userId) {
        return response()->json(['message' => 'User ID not found'], 401);
    }

    // Add user email as modified_by
    $validated['modified_by'] = $userEmail;

    // Proses upload gambar
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imageName = time().'.'.$image->getClientOriginalExtension();
        $image->move(public_path('images'), $imageName);
        $validated['image'] = 'images/'.$imageName; // Simpan path file gambar
    }

    // Create the product including 'name'
    $product = Product::create($validated);

    return response()->json([
        'message' => "Data Berhasil Disimpan",
        'data' => $product
    ], 201);
}
    

    public function read()
    {
        $products = Product::all();
        return response()->json([
            'msg' => 'Data Produk Keseluruhan',
            'data' => $products
        ], 200);
    }

    public function update(Request $request, $id)
{
    $data = JWT::decode($request->bearerToken(), new Key(env('JWT_SECRET_KEY'), 'HS256'));
    $user = User::find($data->id);

    $validator = Validator::make($request->all(), [
        'name' => 'sometimes|string|max:255',
        'description' => 'sometimes|string',
        'price' => 'sometimes|integer',
        'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validate image file
        'category_id' => 'sometimes|string|max:255', // Accept category as name
        'expired_at' => 'sometimes|date',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->messages())->setStatusCode(422);
    }

    $validated = $validator->validated();

    // Handle category input (name)
    if (isset($validated['category_id'])) {
        $categoryName = $validated['name'];
        $category = Category::firstOrCreate(['name' => $categoryName]);

        if (!$category) {
            return response()->json(['message' => 'Invalid category'], 422);
        }

        $validated['category_id'] = $category->id;
        unset($validated['name']); // Remove the category name as we have the ID now
    }

    // Retrieve user ID and email
    $userId = $user->id;
    $userEmail = $user->email;

    // Ensure user ID is valid before proceeding
    if (!$userId) {
        return response()->json(['message' => 'User ID not found'], 401);
    }

    // Add user email as modified_by
    $validated['modified_by'] = $userEmail;

    $product = Product::find($id);

    if ($product) {
        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
            $validated['image'] = 'images/'.$imageName; // Save image file path

            // Delete old image if exists
            if ($product->image) {
                $oldImagePath = public_path($product->image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
        }

        $product->update($validated);

        return response()->json([
            'msg' => 'Data with id: ' . $id . ' successfully updated',
            'data' => $product
        ], 200);
    }

    return response()->json([
        'msg' => 'Data with id: ' . $id . ' not found'
    ], 404);
}
    

    public function delete($id)
    {
        $product = Product::find($id);

        if ($product) {
            $product->delete();

            return response()->json([
                'msg' => 'Data produk dengan ID: ' . $id . ' berhasil dihapus'
            ], 200);
        }

        return response()->json([
            'msg' => 'Data produk dengan ID: ' . $id . ' tidak ditemukan',
        ], 404);
    }
}