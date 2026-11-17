<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\StatsController as ApiStatsController;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_buku' => 0,
            'total_anggota' => 0,
            'peminjaman_aktif' => 0,
        ];

        try {
            $apiUrl = config('services.internal_api.base_url');
            if ($apiUrl) {
                $response = Http::timeout(1)->get($apiUrl.'/api/stats');
                if ($response->successful()) {
                    $stats = $response->json();
                } else {
                    $stats = (new ApiStatsController)->index()->getData(true);
                }
            } else {
                $stats = (new ApiStatsController)->index()->getData(true);
            }
        } catch (\Exception $e) {
            // Fallback langsung ke API controller internal / database agar dashboard selalu menyajikan data riil
            $stats = (new ApiStatsController)->index()->getData(true);
        }

        return view('dashboard', compact('stats'));
    }
}
