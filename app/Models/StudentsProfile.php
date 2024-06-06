<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentsProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'serie',
        'class'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
