<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiscoverController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::with('user', 'requests');

        if ($request->filled('date')) {
            $query->whereDate('concert_date', '=', $request->date);
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        if ($request->filled('country')) {
            $query->where('country', 'like', '%' . $request->country . '%');
        }

        if ($request->filled('genre')) {
            $query->where('genre', 'like', '%' . $request->genre . '%');
        }

        if ($request->filled('search')) {
            $query->where('concert_name', 'like', '%' . $request->search . '%');
        }

        $posts = $query->latest()->paginate(10);

        return view('discover', compact('posts'));
    }

    public function createPost(Request $request)
    {
        $validated = $request->validate([
            'concert_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'description' => 'required|string',
            'concert_date' => 'required|date',
            'concert_time' => 'required',
            'spots_available' => 'required|integer|min:1',
            'cover_image' => 'nullable|image|max:2048',
            'cover_color' => 'nullable|string',
        ]);

        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('covers', 'public');
            $validated['cover_image'] = $path;
        }

        $validated['user_id'] = Auth::id();
        Post::create($validated);

        return redirect()->route('discover')->with('success', 'Post created successfully!');
    }

    public function joinPost(Post $post)
    {
        $user = Auth::user();

        PostRequest::firstOrCreate(
            ['post_id' => $post->id, 'user_id' => $user->id],
            ['status' => 'pending']
        );

        return back()->with('success', 'Join request sent!');
    }
}
