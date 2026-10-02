<?php

namespace App\Http\Controllers\Call;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\User;
use App\Notifications\NewRoom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CallController extends Controller
{
    /**
     * I-show ang lista sa video call rooms.
     */
    public function index(): Response
    {
        $rooms = Room::with('creator:id,name')
            ->latest()
            ->get()
            ->map(fn (Room $room) => [
                'id'           => $room->id,
                'title'        => $room->title,
                'description'  => $room->description,
                'room_code'    => $room->room_code,
                'status'       => $room->status,
                'created_by'   => $room->creator?->name ?? '—',
                'scheduled_at' => $room->scheduled_at?->format('M d, Y g:i A'),
                'created_at'   => $room->created_at->diffForHumans(),
            ]);

        return Inertia::render('Call/Index', [
            'rooms'     => $rooms,
            'canCreate' => Auth::user()->hasAnyRole(['admin', 'leader']),
            'isAdmin'   => Auth::user()->hasRole('admin'),
        ]);
    }

    /**
     * I-show ang Jitsi call room — mag-validate sa room code una.
     */
    public function show(Request $request, Room $room): Response|RedirectResponse
    {
        // I-check kung active pa ang room
        if (! $room->isActive()) {
            return redirect()->route('calls.index')
                ->with('error', 'This room has been closed.');
        }

        // I-validate ang room code kung member (dili admin/leader)
        if (! Auth::user()->hasAnyRole(['admin', 'leader'])) {
            $request->validate([
                'code' => ['required', 'string'],
            ]);

            if ($request->code !== $room->room_code) {
                return redirect()->route('calls.index')
                    ->with('error', 'Invalid room code. Please check with your upline.');
            }
        }

        return Inertia::render('Call/Show', [
            'room' => [
                'id'          => $room->id,
                'title'       => $room->title,
                'description' => $room->description,
                'jitsi_url'   => $room->getJitsiUrl(),
                'jitsi_room'  => $room->jitsi_room,
                'room_code'   => $room->room_code,
                'status'      => $room->status,
                'created_by'  => $room->creator?->name ?? '—',
                'scheduled_at'=> $room->scheduled_at?->format('M d, Y g:i A'),
            ],
            'isAdmin'   => Auth::user()->hasRole('admin'),
            'canCreate' => Auth::user()->hasAnyRole(['admin', 'leader']),
            'userName'  => Auth::user()->name,
        ]);
    }

    /**
     * I-create ang bag-ong room — admin/leader lang.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string', 'max:1000'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
        ]);

        $room = Room::create([
            'title'        => $validated['title'],
            'description'  => $validated['description'] ?? null,
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'created_by'   => Auth::id(),
        ]);

        // I-notify ang tanan nga members — bag-ong video call room
        User::all()->each(fn (User $u) => $u->id !== Auth::id()
            ? $u->notify(new NewRoom($room))
            : null
        );

        return redirect()->route('calls.index')
            ->with('success', 'Room created successfully.');
    }

    /**
     * I-close ang room — admin lang.
     */
    public function close(Room $room): RedirectResponse
    {
        $room->update(['status' => 'closed']);

        return back()->with('success', "Room '{$room->title}' has been closed.");
    }
}
