<?php

namespace App\Http\Controllers\Network;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class NetworkController extends Controller
{
    /**
     * I-show ang network tree page.
     * Default root — ang current user mismo kung walay ?root= sa URL.
     * Kung naa ?root=ID, ang maong user ang i-render as root.
     */
    public function index(): Response
    {
        $currentUser = Auth::user();

        // Admin makakita sa tibuok network — default root is admin
        // Member makakita sa iyang sariling network lang
        $rootId = request()->query('root', $currentUser->id);

        // I-validate — members dili makaka-view sa labaw sa ilang upline chain
        $rootUser = User::findOrFail($rootId);

        // I-build ang tree data recursively — max 4 levels para sa performance
        $tree = $this->buildTree($rootUser, 4);

        // Breadcrumb — para makabalik sa parent nodes
        $breadcrumb = $this->buildBreadcrumb($rootUser, $currentUser);

        return Inertia::render('Network/Index', [
            'tree' => $tree,
            'rootId' => (int) $rootId,
            'breadcrumb' => $breadcrumb,
            'isAdmin' => $currentUser->hasRole('admin'),
        ]);
    }

    /**
     * I-build ang tree recursively.
     * Ibalik ang node with its children.
     *
     * @return array<string, mixed>
     */
    private function buildTree(User $user, int $maxDepth, int $currentDepth = 0): array
    {
        $node = [
            'id' => $user->id,
            'name' => $user->name,
            'status' => $user->status,
            'roles' => $user->getRoleNames()->toArray(),
            'children' => [],
        ];

        // I-stop kung naabot na ang max depth
        if ($currentDepth >= $maxDepth) {
            // I-indicate kung naa pay mas daghan nga children para ma-drill
            $node['has_more'] = $user->downlines()->exists();

            return $node;
        }

        // I-load ang direct downlines
        $children = $user->downlines()->orderBy('name')->get();

        foreach ($children as $child) {
            $node['children'][] = $this->buildTree($child, $maxDepth, $currentDepth + 1);
        }

        $node['has_more'] = false;

        return $node;
    }

    /**
     * I-build ang breadcrumb — chain gikan sa current root pabalik sa
     * original root (current user o platform root).
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildBreadcrumb(User $rootUser, User $currentUser): array
    {
        $breadcrumb = [];
        $node = $rootUser;

        // I-traverse paibabaw hangtod sa current user o platform root
        $visited = [];
        while ($node && ! in_array($node->id, $visited)) {
            array_unshift($breadcrumb, [
                'id' => $node->id,
                'name' => $node->name,
            ]);
            $visited[] = $node->id;

            // I-stop kung naabot na ang current user
            if ($node->id === $currentUser->id) {
                break;
            }

            $node = $node->upline;
        }

        return $breadcrumb;
    }
}
