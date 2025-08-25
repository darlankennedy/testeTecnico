<?php

namespace Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class profile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'phone',
        'avatar',
        'bio',
        'address',
        'birthdate',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
