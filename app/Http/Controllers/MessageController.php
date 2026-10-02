<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class MessageController extends Controller
{
    /**
     * I-show ang conversations list — pag-abot dinhi makita ang tanan nga chats.
     */
    public function index(): Response
    {
        $userId = Auth::id();

        // I-load ang tanan nga conversations sa current user
        $conversations = Conversation::with([
            'participantOne:id,name',
            'participantTwo:id,name',
            'latestMessage',
        ])
            ->where('user_one', $userId)
            ->orWhere('user_two', $userId)
            ->orderByDesc('last_message_at')
            ->get()
            ->map(function (Conversation $conv) use ($userId) {
                $other = $conv->otherParticipant($userId);
                $latest = $conv->latestMessage;
                $unread = $conv->unreadCountFor($userId);

                return [
                    'id' => $conv->id,
                    'other_id' => $other->id,
                    'other_name' => $other->name,
                    'other_avatar' => strtoupper($other->name[0]),
                    'last_message' => $latest?->body ?? 'No messages yet.',
                    'last_at' => $latest?->created_at->diffForHumans() ?? '',
                    'unread' => $unread,
                    'is_mine' => $latest?->sender_id === $userId,
                ];
            });

        // I-load ang members para sa "New Message" button
        $members = User::where('id', '!=', $userId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Messages/Index', [
            'conversations' => $conversations,
            'members' => $members,
            'activeId' => null,
            'messages' => [],
            'otherUser' => null,
        ]);
    }

    /**
     * I-show ang specific conversation thread.
     */
    public function show(Conversation $conversation): Response|RedirectResponse
    {
        $userId = Auth::id();

        // I-verify nga ang current user kay participant
        if ($conversation->user_one !== $userId && $conversation->user_two !== $userId) {
            abort(403, 'You are not part of this conversation.');
        }

        // I-mark as read ang tanan nga unread messages sa other person
        $conversation->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // I-load ang messages — latest 50
        $messages = $conversation->messages()
            ->with('sender:id,name')
            ->latest()
            ->limit(50)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (Message $msg) => [
                'id' => $msg->id,
                'body' => $msg->body,
                'sender_id' => $msg->sender_id,
                'sender' => $msg->sender->name,
                'is_mine' => $msg->sender_id === $userId,
                'read' => ! is_null($msg->read_at),
                'created_at' => $msg->created_at->format('g:i A'),
                'date' => $msg->created_at->format('M d, Y'),
            ]);

        $other = $conversation->otherParticipant($userId);

        // I-load ang conversations list para sa sidebar
        $conversations = Conversation::with([
            'participantOne:id,name',
            'participantTwo:id,name',
            'latestMessage',
        ])
            ->where('user_one', $userId)
            ->orWhere('user_two', $userId)
            ->orderByDesc('last_message_at')
            ->get()
            ->map(function (Conversation $conv) use ($userId) {
                $o = $conv->otherParticipant($userId);
                $latest = $conv->latestMessage;

                return [
                    'id' => $conv->id,
                    'other_id' => $o->id,
                    'other_name' => $o->name,
                    'other_avatar' => strtoupper($o->name[0]),
                    'last_message' => $latest?->body ?? 'No messages yet.',
                    'last_at' => $latest?->created_at->diffForHumans() ?? '',
                    'unread' => $conv->unreadCountFor($userId),
                    'is_mine' => $latest?->sender_id === $userId,
                ];
            });

        $members = User::where('id', '!=', $userId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Messages/Index', [
            'conversations' => $conversations,
            'members' => $members,
            'activeId' => $conversation->id,
            'messages' => $messages,
            'otherUser' => [
                'id' => $other->id,
                'name' => $other->name,
                'avatar' => strtoupper($other->name[0]),
            ],
        ]);
    }

    /**
     * I-start ang bag-ong conversation o punta sa existing.
     */
    public function startOrOpen(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id', 'different:'.Auth::id()],
        ]);

        $conversation = Conversation::findOrCreateBetween(Auth::id(), $request->user_id);

        return redirect()->route('messages.show', $conversation->id);
    }

    /**
     * I-send ang bag-ong message.
     */
    public function send(Request $request, Conversation $conversation): RedirectResponse
    {
        $userId = Auth::id();

        // I-verify participant
        if ($conversation->user_one !== $userId && $conversation->user_two !== $userId) {
            abort(403);
        }

        $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        // I-create ang message
        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $userId,
            'body' => $request->body,
        ]);

        // I-update ang last_message_at sa conversation
        $conversation->update(['last_message_at' => now()]);

        return back();
    }

    /**
     * I-poll ang bag-ong messages — gi-call sa Vue every few seconds.
     */
    public function poll(Conversation $conversation): JsonResponse
    {
        $userId = Auth::id();

        if ($conversation->user_one !== $userId && $conversation->user_two !== $userId) {
            abort(403);
        }

        // I-mark as read dayon
        $conversation->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = $conversation->messages()
            ->with('sender:id,name')
            ->latest()
            ->limit(50)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (Message $msg) => [
                'id' => $msg->id,
                'body' => $msg->body,
                'sender_id' => $msg->sender_id,
                'sender' => $msg->sender->name,
                'is_mine' => $msg->sender_id === $userId,
                'read' => ! is_null($msg->read_at),
                'created_at' => $msg->created_at->format('g:i A'),
                'date' => $msg->created_at->format('M d, Y'),
            ]);

        return response()->json(['messages' => $messages]);
    }
}
