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
        'edition',
        'active',
        'image',
    ];

    public function rating()
    {
        return $this->hasMany(Rating::class, 'book_id', 'id');
    }

    public function lendings()
    {
        return $this->hasMany(Lendings::class, 'book_id', 'id');
    }

    public function wishList()
    {
        return $this->hasMany(WishList::class, 'book_id', 'id');
    }
}
