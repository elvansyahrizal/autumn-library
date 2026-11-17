<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    protected $fillable = [
        'member_id',
        'user_id',
        'approved_by',
        'approved_at',
        'tanggal_pinjam',
        'tanggal_kembali',
        'tanggal_dikembalikan',
        'status',
        'denda',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pinjam' => 'date',
            'tanggal_kembali' => 'date',
            'tanggal_dikembalikan' => 'date',
            'approved_at' => 'datetime',
            'denda' => 'integer',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function loanItems(): HasMany
    {
        return $this->hasMany(LoanItem::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'diajukan';
    }

    public function isApproved(): bool
    {
        return $this->status === 'dipinjam';
    }

    public function isReturned(): bool
    {
        return $this->status === 'dikembalikan';
    }

    public function isRejected(): bool
    {
        return $this->status === 'ditolak';
    }
}
