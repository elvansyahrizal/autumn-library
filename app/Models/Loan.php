<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    /** Tarif denda keterlambatan per hari (Rupiah). */
    public const FINE_PER_DAY = 5000;

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

    /**
     * Transaksi yang masih menahan stok buku (diajukan / dipinjam).
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['diajukan', 'dipinjam'], true);
    }

    /**
     * Selisih hari antara jatuh tempo dan tanggal kembali (0 bila belum telat).
     */
    public function lateDays(?CarbonInterface $returnedAt = null): int
    {
        if (! $this->tanggal_kembali) {
            return 0;
        }

        $returned = ($returnedAt ?? now())->copy()->startOfDay();
        $due = $this->tanggal_kembali->copy()->startOfDay();

        // Carbon 3 mengembalikan nilai relatif; ambil nilai absolut setelah dibalik.
        $diff = (int) $due->diffInDays($returned, false);

        return max(0, $diff);
    }

    /**
     * Denda berjalan / final untuk transaksi ini.
     */
    public function estimatedFine(?CarbonInterface $returnedAt = null): int
    {
        return $this->lateDays($returnedAt) * self::FINE_PER_DAY;
    }
}
