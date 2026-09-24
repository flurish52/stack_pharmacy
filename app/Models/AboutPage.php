<?php

namespace App\Models;

use App\Models\Concerns\HasCloudinaryImage;
use Illuminate\Database\Eloquent\Model;

class AboutPage extends Model
{
    use HasCloudinaryImage;

    protected $fillable = ['story', 'mission', 'vision']; // image_public_id is merged in by the trait

    public static function current(): self
    {
        return static::query()->first() ?? static::create();
    }
}
