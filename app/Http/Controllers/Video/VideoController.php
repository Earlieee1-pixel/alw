<?php

namespace App\Http\Controllers\Video;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Video;
use App\Notifications\NewVideo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class VideoController extends Controller
{
    /**
     * I-show ang training video library — may filter sa category.
     */
    public function index(Request $request): Response
    {
        $videos = Video::with('poster:id,name')
            ->where('is_published', true)
            ->when($request->category, fn ($q, $c) => $q->where('category', $c))
            ->when($request->search, fn ($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Video/Index', [
            'videos'     => $videos->through(fn (Video $v) => [
                'id'          => $v->id,
                'title'       => $v->title,
                'description' => $v->description,
                'category'    => $v->category,
                'source'      => $v->source,
                'thumbnail'   => $v->getThumbnailUrl(),
                'posted_by'   => $v->poster?->name ?? '—',
                'posted_at'   => $v->created_at->diffForHumans(),
            ]),
            'filters'    => $request->only(['category', 'search']),
            'categories' => [
                'onboarding', 'leadership', 'network',
                'compliance', 'product', 'general',
            ],
            'canPost'    => Auth::user()->hasAnyRole(['admin', 'leader']),
        ]);
    }

    /**
     * I-show ang single video watch page.
     */
    public function show(Video $video): Response
    {
        // I-check kung published — members dili makakita sa drafts
        abort_if(! $video->is_published && ! Auth::user()->hasRole('admin'), 403);

        // Related videos — same category, excluding current
        $related = Video::where('category', $video->category)
            ->where('id', '!=', $video->id)
            ->where('is_published', true)
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Video $v) => [
                'id'        => $v->id,
                'title'     => $v->title,
                'thumbnail' => $v->getThumbnailUrl(),
                'category'  => $v->category,
                'posted_at' => $v->created_at->diffForHumans(),
            ]);

        return Inertia::render('Video/Show', [
            'video' => [
                'id'          => $video->id,
                'title'       => $video->title,
                'description' => $video->description,
                'embed_url'   => $video->getEmbedUrl(),
                'category'    => $video->category,
                'source'      => $video->source,
                'posted_by'   => $video->poster?->name ?? '—',
                'posted_at'   => $video->created_at->format('M d, Y'),
            ],
            'related'  => $related,
            'canPost'  => Auth::user()->hasAnyRole(['admin', 'leader']),
            'canDelete'=> Auth::user()->hasRole('admin'),
        ]);
    }

    /**
     * I-store ang bag-ong training video — admin/leader lang.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'url'         => ['required', 'url'],
            'source'      => ['required', 'in:youtube,vimeo,other'],
            'category'    => ['required', 'in:onboarding,leadership,network,compliance,product,general'],
        ]);

        // I-extract ang video ID gikan sa URL
        $videoId = Video::extractVideoId($validated['url'], $validated['source']);

        Video::create([
            'title'        => $validated['title'],
            'description'  => $validated['description'],
            'embed_url'    => $validated['url'],
            'video_id'     => $videoId,
            'source'       => $validated['source'],
            'category'     => $validated['category'],
            'is_published' => true,
            'posted_by'    => Auth::id(),
        ]);

        // I-notify ang tanan nga members — bag-ong training video
        $video = Video::latest()->first();
        User::all()->each(fn (User $u) => $u->id !== Auth::id()
            ? $u->notify(new NewVideo($video))
            : null
        );

        return redirect()->route('videos.index')
            ->with('success', 'Video posted successfully.');
    }

    /**
     * I-delete ang video — admin lang.
     */
    public function destroy(Video $video): RedirectResponse
    {
        $video->delete();

        return redirect()->route('videos.index')
            ->with('success', 'Video deleted.');
    }
}
