<?php

namespace App\Models;

use App\Support\Format;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = ['user_id', 'title', 'message', 'type', 'is_read'];

    protected function casts(): array
    {
        return ['is_read' => 'boolean'];
    }

    public function getCreatedAtFormattedAttribute(): string
    {
        return Format::dateTime($this->created_at);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
