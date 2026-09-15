<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['name', 'description', 'whatsapp_message', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function whatsappUrl(): string
    {
        $phone = config('services.pharmacy_whatsapp_number');

        return "https://wa.me/{$phone}?text=" . urlencode($this->whatsapp_message);
    }
}
