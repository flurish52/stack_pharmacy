<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasCloudinaryImage;
use Spatie\Activitylog\Support\LogOptions;

class Service extends Model
{
    use HasCloudinaryImage;

    protected $fillable = ['name', 'description', 'whatsapp_message', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function whatsappUrl(): string
    {
        $phone = config('services.pharmacy_whatsapp_number');

        return "https://wa.me/{$phone}?text=" . urlencode($this->whatsapp_message);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('service')
            ->logOnly(['name', 'description', 'is_active', 'whatsapp_message', 'image_public_id'])
            ->logOnlyDirty();
    }
}
