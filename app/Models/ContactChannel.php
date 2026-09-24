<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class ContactChannel extends Model
{ use LogsActivity;
    protected $fillable = ['platform', 'handle', 'url', 'display_order'];



    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('product')
            ->logOnly(['platform', 'handle', 'url', 'display_order'])
            ->logOnlyDirty();
    }
}
