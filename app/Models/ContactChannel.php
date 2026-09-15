<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactChannel extends Model
{
    protected $fillable = ['platform', 'handle', 'url', 'display_order'];
}
