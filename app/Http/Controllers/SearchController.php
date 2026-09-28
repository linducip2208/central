<?php

namespace App\Http\Controllers;

use App\Services\GlobalSearchService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request, GlobalSearchService $search)
    {
        $q = (string) $request->get('q', '');
        $groups = $search->search($request->user(), $q);

        if ($request->wantsJson() || $request->get('format') === 'json') {
            return response()->json(['q' => $q, 'groups' => $groups]);
        }

        return view('search.index', compact('groups', 'q'));
    }
}
