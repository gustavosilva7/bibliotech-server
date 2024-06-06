<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Books extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'publisher',
        'year',
        'tag',
        'quantity',
        'edition',
        'active',
    ];

    public function rating()
    {
        return $this->hasMany(Rating::class);
    }

    public function lendings()
    {
        return $this->hasMany(Lendings::class);
    }
}
