<?php

namespace App\Models;

use App\Support\Format;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class File extends Model
{
    public const STATUS_ENCRYPTED = 'encrypted';
    public const STATUS_DECRYPTED = 'decrypted';

    protected $fillable = [
        'user_id', 'original_name', 'stored_name', 'original_extension', 'mime_type',
        'original_size', 'processed_size', 'storage_path', 'status',
    ];

    protected function casts(): array
    {
        return ['original_size' => 'integer', 'processed_size' => 'integer'];
    }

    public function getSizeHumanAttribute(): string
    {
        return Format::bytes($this->original_size);
    }

    public function getTypeLabelAttribute(): string
    {
        return strtoupper($this->original_extension);
    }

    public function getCreatedAtFormattedAttribute(): string
    {
        return Format::dateTime($this->created_at);
    }

    /** Nama file saat diunduh. */
    public function getDownloadNameAttribute(): string
    {
        return $this->status === self::STATUS_ENCRYPTED
            ? $this->original_name . '.' . config('securefile.encrypted_extension')
            : $this->original_name;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}
