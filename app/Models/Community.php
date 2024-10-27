<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Community extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'creator_id',
        'slogan',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'community_user', 'community_id', 'user_id')
            ->withPivot('is_creator', 'joined_at');
    }

    public function posts()
    {
        return $this->hasMany(CommunityPost::class);
    }
}
