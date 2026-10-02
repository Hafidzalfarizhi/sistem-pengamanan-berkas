<?php

namespace App\Models;

use App\Support\Format;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    // Tabel hanya memiliki created_at.
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'file_id', 'activity', 'file_name', 'file_size',
        'status', 'description', 'ip_address',
    ];

    protected function casts(): array
    {
        return ['file_size' => 'integer', 'created_at' => 'datetime'];
    }

    public function getSizeHumanAttribute(): string
    {
        return $this->file_size === null ? '-' : Format::bytes($this->file_size);
    }

    public function getCreatedAtFormattedAttribute(): string
    {
        return Format::dateTime($this->created_at);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }
}
