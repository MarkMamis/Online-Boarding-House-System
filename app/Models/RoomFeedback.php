<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomFeedback extends Model
{
    protected $table = 'room_feedbacks';

    protected $fillable = [
        'room_id',
        'user_id',
        'rating',
        'comment',
        'display_name',
        'sentiment_label',
        'sentiment_score',
    ];

    protected $casts = [
        'sentiment_score' => 'decimal:4',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** The name shown publicly (anonymous if display_name is null) */
    public function getPublicNameAttribute(): string
    {
        return $this->display_name ?: 'Anonymous';
    }
}
