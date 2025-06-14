<?php

namespace App\Http\Controllers;

use App\Models\ChatRoom;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
// Import the event for broadcasting
// use App\Events\ChatMessageSent; 

class ChatController extends Controller
{
    /**
     * Display a listing of chat rooms for the authenticated user.
     * GET /api/chats
     */
    public function index()
    {
        $user = Auth::user();

        $chatRooms = ChatRoom::whereHas('participants', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with([
            'lastMessage' => function ($query) {
                $query->with('user:id,name'); // Eager load sender of last message
            },
            'users:id,name' // Eager load all participants in the room
        ])
        ->get()
        ->map(function ($room) use ($user) {
            // Determine the chat room name for direct messages
            if (!$room->is_group && $room->users->count() > 1) {
                $otherUser = $room->users->firstWhere('id', '!=', $user->id);
                $room->name = $otherUser ? $otherUser->name : 'مستخدم محذوف';
            }
            return $room;
        });

        return response()->json($chatRooms);
    }

    /**
     * Fetch messages for a specific chat room.
     * GET /api/chats/{chatRoom}/messages
     */
    public function show(ChatRoom $chatRoom)
    {
        // Authorization: Check if the current user is a participant of the room
        if (!Auth::user()->chatRooms()->where('chat_room_id', $chatRoom->id)->exists()) {
            return response()->json(['message' => 'غير مصرح لك بعرض هذه المحادثة.'], 403);
        }

        $messages = $chatRoom->messages()
            ->with('user:id,name') // Eager load the sender's name
            ->latest() // Get the latest messages first
            ->paginate(50); // Paginate the results

        // We reverse the items on the frontend to display correctly (oldest at the top)
        return response()->json($messages);
    }

    /**
     * Store a newly created message in storage.
     * POST /api/chats/{chatRoom}/messages
     */
    public function store(Request $request, ChatRoom $chatRoom)
    {
        // Authorization: Check if the current user can send a message in this room
        if (!Auth::user()->chatRooms()->where('chat_room_id', $chatRoom->id)->exists()) {
            return response()->json(['message' => 'غير مصرح لك بإرسال رسائل في هذه المحادثة.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'body' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        $message = $chatRoom->messages()->create([
            'user_id' => $user->id,
            'body' => $request->body,
        ]);
        
        // Load the sender relationship for broadcasting
        $message->load('user:id,name');

        // Broadcast the new message to other participants
        // This requires setting up Laravel Echo and a broadcasting driver like Pusher.
        // broadcast(new ChatMessageSent($message))->toOthers();

        return response()->json($message, 201);
    }
}

