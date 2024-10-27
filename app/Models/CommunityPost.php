<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'community_id',
        'title',
        'content'
    ];

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function comments()
    {
        return $this->hasMany(CommunityPostComment::class, 'post_id');
    }
}
