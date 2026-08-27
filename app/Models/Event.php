<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = ['title_bn', 'title_en', 'description', 'event_date', 'event_time', 'location', 'image', 'is_active'];

    protected function casts(): array
    {
        return ['event_date' => 'date', 'is_active' => 'boolean'];
    }
}
