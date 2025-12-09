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

        // Date filter
        if ($request->filled('date_filter')) {
            $dateFilter = $request->date_filter;
            $now = now();
            
            switch ($dateFilter) {
                case 'today':
                    $query->whereDate('concert_date', $now->toDateString());
                    break;
                case 'this_week':
                    // From today until end of this week (Sunday)
                    $startOfWeek = $now->copy()->startOfDay();
                    $endOfWeek = $now->copy()->endOfWeek()->endOfDay();
                    $query->whereBetween('concert_date', [$startOfWeek, $endOfWeek]);
                    break;
                case 'this_month':
                    $query->whereMonth('concert_date', $now->month)
                          ->whereYear('concert_date', $now->year);
                    break;
            }
        }

        // Location filter (search in both city and location fields)
        if ($request->filled('location_filter')) {
            $location = trim($request->location_filter);
            if (!empty($location)) {
                $query->where(function($q) use ($location) {
                    $q->where('city', 'like', '%' . $location . '%')
                      ->orWhere('location', 'like', '%' . $location . '%');
                });
            }
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
