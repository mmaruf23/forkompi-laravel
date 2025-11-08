<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Post::latest()->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'status' => ['nullable', Rule::in(['draft', 'published'])],
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        $baseSlug = Str::slug($validated['title']);
        $slug = $baseSlug;
        $i = 1;
        while (Post::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $i++;
        }

        $path = null;

        if($request->hasFile("featured_image")) {
            $filename = $slug . "." . $request->file('featured_image')->getClientOriginalExtension();
            $path = $request->file("featured_image")->storeAs('posts', $filename, 'public');
        }


        $post = Post::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'slug' => $slug,
            'excerpt' => $validated['excerpt'] ?? Str::limit(strip_tags($validated['content']), 150),
            'content' => $validated['content'],
            'featured_image' => $path,
            'status' => $validated['status'] ?? 'draft',
            'published_at' => null
         ]);

        return \response()->json([
            'message' => "Posy created successfully",
            'data' => $post
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $post = Post::find($id);

        if (! $post) {
            return \response()->json(['message' => 'Post not found'], 404);
        }

        return \response()->json($post);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $post = Post::find($id);
        
        if (! $post) {
            return response()->json(['message' => 'Post not found'], 404);
        }
        
        $this->authorize('update', $post);
        
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'excerpt' => 'sometimes|string',
            'featured_image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);


        $slug = $post->slug;
        if (isset($validated['title']) && $validated['title'] !== $post->title) {
            $baseSlug = Str::slug($validated['title']);
            $slug = $baseSlug;
            $i = 1;
            while (Post::where('slug', $slug)->where('id', '!=', $post->id)->exists()) {
                $slug = $baseSlug . '-' . $i++;
            }
            $validated['slug'] = $slug;
        }

        if (isset($validated['content']) && !isset($validated['excerpt'])) {
            $validated['excerpt'] = Str::limit(strip_tags($validated['content']), 150);
        }

        if ($request->hasFile("featured_image")) {
            if ($post->featured_image){
                Storage::disk('public')->delete($post->featured_image);
            }

            $filename = $slug . "." . $request->file('featured_image')->getClientOriginalExtension();
            $path = $request->file("featured_image")->storeAs('posts', $filename, 'public');
            $validated['featured_image'] = $path;

        }

        $post->update($validated);

        return response()->json([
            'message' => 'Post updated successfully',
            'data' => $post
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $post = Post::find($id);

        if (! $post) {
            return \response()->json(['message' => "Post not found"], 404);
        }

        $this->authorize('delete', $post);

        $post->delete();

        return \response()->json([
            'message' => "Post deleted successfully"
        ]);
    }
}
