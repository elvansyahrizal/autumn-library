<?php

namespace App\Http\Controllers;

use App\Services\LibraryStatsService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index(LibraryStatsService $statsService)
    {
        $stats = null;

        try {
            $apiUrl = config('services.internal_api.base_url');

            if ($apiUrl) {
                $response = Http::timeout((int) config('services.internal_api.timeout', 2))
                    ->get($apiUrl.'/api/stats');

                if ($response->successful()) {
                    $stats = $response->json();
                }
            }
        } catch (\Throwable $e) {
            Log::info('Dashboard memakai data lokal karena API internal tidak tersedia.', [
                'message' => $e->getMessage(),
            ]);
        }

        // Fallback lokal: hasilnya sama dengan payload GET /api/stats.
        $stats = array_merge($statsService->summary(), is_array($stats) ? $stats : []);

        return view('dashboard', compact('stats'));
    }
}
