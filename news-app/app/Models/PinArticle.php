<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PinArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_session',
        'article_id',
        'title',
        'url',
        'date',
        'section_id',
        'section_name',
    ];
}