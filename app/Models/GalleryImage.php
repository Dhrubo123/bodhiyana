<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GalleryImage extends Model
{
    protected $fillable = ['title_bn', 'title_en', 'image_path', 'display_order', 'is_active'];
    protected $appends = ['image_url'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path || ! Storage::disk('public')->exists($this->image_path)) return null;
        return '/api/gallery-assets/'.ltrim($this->image_path, '/').'?v='.Storage::disk('public')->lastModified($this->image_path);
    }
}
