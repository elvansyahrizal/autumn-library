<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\LoanRuleException;
use App\Http\Controllers\Controller;
use App\Http\Resources\LoanResource;
use App\Models\Loan;
use App\Models\Member;
use App\Models\User;
use App\Services\LoanReportService;
use App\Services\LoanService;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function index(Request $request, LoanReportService $report)
    {
        return LoanResource::collection(
            $report->paginate((int) $request->query('page', 1))
        );
    }

    public function store(Request $request, LoanService $loans)
    {
        $validated = $request->validate([
            'member_id' => 'required|integer|exists:members,id',
            'user_id' => 'required|integer|exists:users,id',
            'tanggal_pinjam' => 'required|date',
            'tanggal_kembali' => 'required|date|after_or_equal:tanggal_pinjam',
            'book_ids' => 'required|array|min:1',
            'book_ids.*' => 'integer|exists:books,id',
        ], [
            'member_id.required' => 'Anggota wajib dipilih.',
            'user_id.required' => 'Petugas (user_id) wajib diisi.',
            'user_id.exists' => 'Petugas yang dipilih tidak valid.',
            'tanggal_pinjam.required' => 'Tanggal pinjam wajib diisi.',
            'tanggal_kembali.required' => 'Tanggal kembali wajib diisi.',
            'tanggal_kembali.after_or_equal' => 'Tanggal kembali tidak boleh sebelum tanggal pinjam.',
            'book_ids.required' => 'Pilih minimal satu buku.',
        ]);

        try {
            $loan = $loans->submitForMember(
                Member::findOrFail($validated['member_id']),
                $validated['book_ids'],
                $validated['tanggal_pinjam'],
                $validated['tanggal_kembali'],
                User::findOrFail($validated['user_id'])
            );
        } catch (LoanRuleException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $loan->load(['member', 'user', 'loanItems.book']);

        return (new LoanResource($loan))->response()->setStatusCode(201);
    }

    public function kembalikan(string $id, LoanService $loans)
    {
        $loan = Loan::findOrFail($id);

        try {
            $loan = $loans->returnLoan($loan);
        } catch (LoanRuleException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $loan->load(['member', 'user', 'loanItems.book']);

        return new LoanResource($loan);
    }
}
