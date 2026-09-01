<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Event extends Model
{
    protected $fillable = ['title_bn', 'title_en', 'description', 'event_date', 'event_time', 'location', 'image', 'is_active'];

    protected $appends = ['image_url'];

    protected function casts(): array
    {
        return ['event_date' => 'date', 'is_active' => 'boolean'];
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image || ! Storage::disk('public')->exists($this->image)) return null;

        return '/api/event-assets/'.ltrim($this->image, '/').'?v='.Storage::disk('public')->lastModified($this->image);
    }
}
