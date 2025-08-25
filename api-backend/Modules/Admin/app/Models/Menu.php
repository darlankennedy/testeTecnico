<?php

namespace Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'Menus';

    protected $fillable = [
        'title','route','icon','permission','parent_id','sort','active'
    ];

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('sort');
    }

    public function scopeActive($q)
    {
        return $q->where('active', true);
    }
}
