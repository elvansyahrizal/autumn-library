<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LibraryStatsService;

class StatsController extends Controller
{
    public function index(LibraryStatsService $stats)
    {
        return response()->json($stats->summary());
    }
}
