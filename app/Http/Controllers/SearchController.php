<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SearchService;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = trim($request->q);

        if(strlen($q) < 2)
		{
            return response()->json([]);
        }

        return response()->json(app(SearchService::class)->search($q));
    }
}
