<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EditorImageUploadController extends Controller
{
    /**
     * Upload an image from the Quill rich text editor.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:5120'],
        ]);

        $path = $request->file('image')->store('editor', 'public');

        return response()->json([
            'success' => true,
            'url'     => asset('storage/' . $path),
            'path'    => $path,
        ]);
    }
}
