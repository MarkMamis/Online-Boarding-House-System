<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotConversation extends Model
{
    protected $fillable = [
        'user_id',
        'role',
    ];

    public function messages()
    {
        return $this->hasMany(ChatbotMessage::class, 'conversation_id')->orderBy('created_at');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
