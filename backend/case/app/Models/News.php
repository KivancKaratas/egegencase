<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;

class News extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'content', 'image'];
}

