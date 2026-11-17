<?php

namespace App\Http\Controllers;

use App\Exceptions\LoanRuleException;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Member;
use App\Services\LoanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LoanController extends Controller
{
    public function index()
    {
        $loans = Loan::with(['member', 'user', 'loanItems.book'])->paginate(10);

        return view('loans.index', compact('loans'));
    }

    public function report(Request $request)
    {
        $body = ['data' => [], 'meta' => null];

        try {
            $apiUrl = config('services.internal_api.base_url');
            if ($apiUrl) {
                $response = Http::timeout((int) config('services.internal_api.timeout', 2))->get($apiUrl.'/api/loans', [
                    'page' => $request->query('page', 1),
                ]);

                if ($response->successful()) {
                    $body = $response->json();
                } else {
                    $body = (new Api\LoanController)->index()->response()->getData(true);
                }
            } else {
                $body = (new Api\LoanController)->index()->response()->getData(true);
            }
        } catch (\Exception $e) {
            // Fallback memanggil controller API internal agar laporan selalu terisi data riil
            $body = (new Api\LoanController)->index()->response()->getData(true);
        }

        return view('loans.report', [
            'loans' => $body['data'] ?? [],
            'meta' => $body['meta'] ?? null,
        ]);
    }

    public function create()
    {
        $members = Member::all();
        $books = Book::all();

        return view('loans.create', compact('members', 'books'));
    }

    public function store(Request $request, LoanService $loans)
    {
        $validated = $request->validate([
            'member_id' => 'required|integer|exists:members,id',
            'tanggal_pinjam' => 'required|date',
            'tanggal_kembali' => 'required|date|after_or_equal:tanggal_pinjam',
            'book_ids' => 'required|array|min:1',
            'book_ids.*' => 'integer|exists:books,id',
        ]);

        try {
            $loans->submitForMember(
                Member::findOrFail($validated['member_id']),
                $validated['book_ids'],
                $validated['tanggal_pinjam'],
                $validated['tanggal_kembali'],
                $request->user()
            );
        } catch (LoanRuleException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('loans.index')
            ->with('success', 'Transaksi peminjaman berhasil dibuat.');
    }

    public function show(string $id)
    {
        $loan = Loan::with(['member', 'user', 'loanItems.book'])->findOrFail($id);

        return view('loans.show', compact('loan'));
    }

    public function edit(string $id)
    {
        $loan = Loan::with(['member', 'user', 'loanItems.book'])->findOrFail($id);

        return view('loans.edit', compact('loan'));
    }

    public function update(Request $request, string $id)
    {
        $loan = Loan::findOrFail($id);

        $validated = $request->validate([
            'tanggal_kembali' => 'required|date|after_or_equal:tanggal_pinjam',
        ], [
            'tanggal_kembali.required' => 'Tanggal kembali wajib diisi.',
            'tanggal_kembali.after_or_equal' => 'Tanggal kembali tidak boleh sebelum tanggal pinjam.',
        ]);

        $loan->update($validated);

        return redirect()->route('loans.index')
            ->with('success', 'Transaksi peminjaman berhasil diperbarui.');
    }

    public function destroy(string $id, LoanService $loans)
    {
        $loan = Loan::findOrFail($id);

        try {
            $loans->delete($loan);
        } catch (LoanRuleException $e) {
            return redirect()->route('loans.index')->with('error', $e->getMessage());
        }

        return redirect()->route('loans.index')
            ->with('success', 'Transaksi peminjaman berhasil dihapus.');
    }

    public function kembalikan(string $id, LoanService $loans)
    {
        $loan = Loan::findOrFail($id);

        try {
            $loans->returnLoan($loan);
        } catch (LoanRuleException $e) {
            return redirect()->route('loans.index')->with('error', $e->getMessage());
        }

        return redirect()->route('loans.index')
            ->with('success', 'Buku berhasil dikembalikan.');
    }
}
