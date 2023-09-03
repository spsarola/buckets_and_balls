<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuggestBucket extends Model
{
    use HasFactory;
    protected $fillable = ['bucket_id', 'ball_id', 'qty', 'volume'];
}
