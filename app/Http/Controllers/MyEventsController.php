<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyEventsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $posts = $user->posts()->latest()->get();

        $totalPosts = $posts->count();
        $totalPendingRequests = $posts->sum(function ($post) {
            return $post->requests()->where('status', 'pending')->count();
        });

        return view('myevents', compact('posts', 'totalPosts', 'totalPendingRequests'));
    }

    public function viewGroupRequests(Post $post)
    {
        $this->authorize('update', $post);

        $pendingRequests = $post->requests()->where('status', 'pending')->with('user')->get();

        return view('myevents.requests', compact('post', 'pendingRequests'));
    }

    public function acceptRequest(PostRequest $request)
    {
        $post = $request->post;
        $this->authorize('update', $post);

        $request->update(['status' => 'accepted']);

        return back()->with('success', 'Request accepted!');
    }

    public function rejectRequest(PostRequest $request)
    {
        $post = $request->post;
        $this->authorize('update', $post);

        $request->update(['status' => 'rejected']);

        return back()->with('success', 'Request rejected!');
    }
}
