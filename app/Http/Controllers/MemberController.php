<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemberRequest;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MemberController extends Controller
{
    public function index()
    {
        $members = Member::when(request('search'), function ($query, $search) {
            return $query->where('nama', 'like', "%{$search}%");
        })->paginate(10);

        return view('members.index', compact('members'));
    }

    public function create()
    {
        return view('members.create');
    }

    public function store(StoreMemberRequest $request)
    {
        $validated = $request->validated();

        $user = User::firstOrCreate(
            ['email' => $validated['email']],
            [
                'name' => $validated['nama'],
                'password' => Hash::make($validated['nim']),
                'role' => 'anggota',
            ]
        );

        $validated['user_id'] = $user->id;
        Member::create($validated);

        return redirect()->route('members.index')
            ->with('success', "Anggota \"{$validated['nama']}\" berhasil ditambahkan (Akun login dibuat dengan password default NIM).");
    }

    public function show(string $id)
    {
        $member = Member::with(['loans.loanItems.book', 'loans.user'])->findOrFail($id);

        return view('members.show', compact('member'));
    }

    public function edit(string $id)
    {
        $member = Member::findOrFail($id);

        return view('members.edit', compact('member'));
    }

    public function update(Request $request, string $id)
    {
        $member = Member::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'nim' => 'required|string|max:20|unique:members,nim,'.$id,
            'email' => 'required|email|max:100|unique:members,email,'.$id,
            'nomor_telepon' => 'required|string|max:15',
            'alamat' => 'required|string',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $member->update($validated);

        return redirect()->route('members.index')
            ->with('success', "Anggota \"{$validated['nama']}\" berhasil diperbarui.");
    }

    public function destroy(string $id)
    {
        $member = Member::with('user')->findOrFail($id);

        if ($member->loans()->exists()) {
            return redirect()->route('members.index')
                ->with('error', "Anggota \"{$member->nama}\" tidak bisa dihapus karena sudah memiliki riwayat peminjaman.");
        }

        $user = $member->user;
        $member->delete();

        if ($user && $user->isAnggota()) {
            $user->delete();
        }

        return redirect()->route('members.index')
            ->with('success', 'Anggota berhasil dihapus.');
    }
}
