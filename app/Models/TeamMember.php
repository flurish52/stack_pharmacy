<?php

namespace App\Models;

use App\Models\Concerns\HasCloudinaryImage;
use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{   use HasCloudinaryImage;

    protected $fillable = ['name', 'role', 'bio', 'sort_order'];

    protected static function booted(): void
    {
        static::addGlobalScope('ordered', fn ($query) => $query->orderBy('sort_order'));
    }
}
