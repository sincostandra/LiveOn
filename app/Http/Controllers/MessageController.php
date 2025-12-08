<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Post;
use App\Models\PostRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $groupChats = $user->posts()
            ->select('posts.id', 'posts.concert_name', 'posts.user_id')
            ->with(['requests' => function ($q) {
                $q->where('status', 'accepted');
            }])
            ->get()
            ->map(function ($post) {
                $post->type = 'group';
                return $post;
            });

        return view('messages', compact('groupChats'));
    }

    public function viewGroupChat(Post $post)
    {
        $user = Auth::user();

        // Get all accepted members for this post (excluding the organizer)
        $memberIds = $post->requests()
            ->where('status', 'accepted')
            ->pluck('user_id')
            ->toArray();

        // Don't include the post creator in the members list
        $members = User::whereIn('id', $memberIds)->get();

        $messages = $post->messages()
            ->with(['sender', 'receiver'])
            ->latest()
            ->get()
            ->reverse();

        return view('messages.group', compact('post', 'members', 'messages'));
    }

    public function sendGroupMessage(Request $request, Post $post)
    {
        $validated = $request->validate([
            'message' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('messages', 'public');
        }

        Message::create([
            'post_id' => $post->id,
            'sender_id' => Auth::id(),
            'message' => $validated['message'] ?? '',
            'image_path' => $imagePath,
        ]);

        return back()->with('success', 'Message sent!');
    }

    public function directMessage(User $user)
    {
        // For direct messages, we'll use post_id = null or create a conversation model
        // For now, redirect to a placeholder or create direct message view
        // This is a placeholder - you may need to implement a conversation system
        return redirect()->route('messages')->with('info', 'Direct messaging coming soon!');
    }
}
