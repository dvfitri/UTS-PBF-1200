<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class categoryController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages())->setStatusCode(422);
        }

        $payload = $validator->validated();
        Category::create([
            'name' => $payload['name'],
        ]);

        return response()->json([
            'message' => 'Data berhasil disimpan'
        ])->setStatusCode(201);
    }
    public function read(){
        $category = Category::all();
        return response()->json([
            'msg' => 'Data Produk Keseluruhan',
            'data' => $category
        ],200);

    }
    
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
 
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages())->setStatusCode(422);
        }
        $valid = $validator->validated();
        $category = Category::findOrFail($id);
        if ($category) {
            Category::where('id', $id)->update($valid);
            return response()->json([
                'message' => 'Data berhasil diupdate'
            ])->setStatusCode(200);
        }
        return response()->json(['data dengan id (' . $id . ')tidak di  temukan']);
    }

    public function delete($id){
        $category = Category::find ($id);

        if ($category) {
            Category::where('id', $id)->delete();

            return response()->json([
                'msg' => 'Data produk dengan ID: '.$id.' berhasil dihapus' 
            ], 200);
        }

        return response()->json([
            'msg' => 'Data produk dengan ID: '.$id.' tidak ditemukan', 
        ],400);
    }

}