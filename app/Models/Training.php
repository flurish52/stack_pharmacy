<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Concerns\HasCloudinaryImage;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Training extends Model
{
    use HasCloudinaryImage, LogsActivity;
    protected $fillable = ['title', 'description', 'image_public_id'];


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('training')
            ->logOnly(['title', 'description', 'image_public_id'])
            ->logOnlyDirty();
    }
}
