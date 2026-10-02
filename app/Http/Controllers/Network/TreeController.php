<?php

namespace App\Http\Controllers\Network;

use App\Http\Controllers\Controller;
use App\Models\TreeNode;
use App\Models\User;
use App\Notifications\TreeNameSet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TreeController extends Controller
{
    /**
     * I-show ang 5-level binary tree.
     * ?root= — ang position_key sa node nga i-render as position 1.
     * Default root key is 'root'.
     */
    public function index(Request $request): Response
    {
        $rootKey = $request->query('root', 'root');

        // I-ensure nga naa ang 31 nodes para sa maong root
        TreeNode::ensureTreeExists($rootKey);

        // I-load ang tanan nga 31 nodes para sa current view
        $nodes = TreeNode::where('position_key', 'like', $rootKey.'%')
            ->whereRaw('LENGTH(position_key) - LENGTH(REPLACE(position_key, "-", "")) <= ?', [
                substr_count($rootKey, '-') + 4, // max 4 dashes from root = 5 levels
            ])
            ->orderBy('display_number')
            ->get()
            ->keyBy('position_key');

        // I-re-number ang display numbers base sa current root (1-31)
        $orderedKeys = $this->getOrderedKeys($rootKey, 5);

        $nodesForView = [];
        foreach ($orderedKeys as $index => $key) {
            $node = $nodes->get($key);
            if ($node) {
                $nodesForView[] = [
                    'id' => $node->id,
                    'position_key' => $node->position_key,
                    'parent_key' => $node->parent_key,
                    'display_number' => $index + 1, // 1-indexed
                    'depth' => $node->depth - substr_count($rootKey, '-'), // relative depth
                    'side' => $node->side,
                    'name' => $node->name,
                    'filled_by' => $node->filler?->name,
                    'filled_at' => $node->filled_at?->diffForHumans(),
                    'is_filled' => $node->isFilled(),
                    // Has children beyond 5 levels?
                    'has_more' => $index >= 15, // level 5 nodes (positions 16-31)
                ];
            }
        }

        // Breadcrumb — split ang root key into navigable parts
        $breadcrumb = $this->buildBreadcrumb($rootKey, $nodes);

        return Inertia::render('Network/Index', [
            'nodes' => $nodesForView,
            'rootKey' => $rootKey,
            'breadcrumb' => $breadcrumb,
        ]);
    }

    /**
     * I-save ang name sa usa ka node — shared sa tanan.
     */
    public function setName(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'position_key' => ['required', 'string', 'exists:tree_nodes,position_key'],
            'name' => ['required', 'string', 'max:100'],
        ]);

        TreeNode::where('position_key', $validated['position_key'])
            ->update([
                'name' => trim($validated['name']),
                'filled_by' => Auth::id(),
                'filled_at' => now(),
            ]);

        // I-notify ang tanan nga admins — nag-update ang network tree
        $node = TreeNode::where('position_key', $validated['position_key'])->first();
        User::role('admin')->where('id', '!=', Auth::id())->each(
            fn (User $admin) => $admin->notify(
                new TreeNameSet($validated['name'], $node->display_number, Auth::user()->name)
            )
        );

        return back()->with('success', 'Name saved successfully.');
    }

    /**
     * I-clear ang name sa usa ka node — admin lang.
     */
    public function clearName(Request $request): RedirectResponse
    {
        $request->validate([
            'position_key' => ['required', 'string', 'exists:tree_nodes,position_key'],
        ]);

        TreeNode::where('position_key', $request->position_key)
            ->update([
                'name' => null,
                'filled_by' => null,
                'filled_at' => null,
            ]);

        return back()->with('success', 'Slot cleared.');
    }

    /**
     * I-generate ang BFS-ordered list sa position keys para sa 5 levels.
     *
     * @return array<int, string>
     */
    private function getOrderedKeys(string $rootKey, int $levels): array
    {
        $keys = [];
        $queue = [['key' => $rootKey, 'depth' => 0]];

        while (! empty($queue)) {
            $current = array_shift($queue);

            if ($current['depth'] >= $levels) {
                continue;
            }

            $keys[] = $current['key'];

            if ($levels > $current['depth'] + 1) {
                $queue[] = ['key' => $current['key'].'-L', 'depth' => $current['depth'] + 1];
                $queue[] = ['key' => $current['key'].'-R', 'depth' => $current['depth'] + 1];
            }
        }

        return $keys;
    }

    /**
     * I-build ang breadcrumb gikan sa root key.
     * e.g. "root-L-R" → [root, root-L, root-L-R]
     *
     * @param  Collection<string, TreeNode>  $nodes
     * @return array<int, array<string, mixed>>
     */
    private function buildBreadcrumb(string $rootKey, $nodes): array
    {
        $breadcrumb = [];
        $parts = explode('-', $rootKey);
        $current = '';

        foreach ($parts as $i => $part) {
            $current = $i === 0 ? $part : $current.'-'.$part;
            $node = $nodes->get($current) ?? TreeNode::where('position_key', $current)->first();

            $breadcrumb[] = [
                'key' => $current,
                'name' => $node?->name ?? ($current === 'root' ? 'Root' : 'Position '.($i + 1)),
            ];
        }

        return $breadcrumb;
    }
}
