<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title_bn', 'title_en', 'subtitle_bn', 'subtitle_en', 'desktop_image',
        'mobile_image', 'button_text', 'button_link', 'display_order', 'is_active',
        'start_date', 'end_date',
    ];

    protected $appends = ['desktop_image_url', 'mobile_image_url'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'start_date' => 'date', 'end_date' => 'date'];
    }

    public function getDesktopImageUrlAttribute(): string
    {
        return '/storage/'.ltrim($this->desktop_image, '/');
    }

    public function getMobileImageUrlAttribute(): ?string
    {
        return $this->mobile_image ? '/storage/'.ltrim($this->mobile_image, '/') : null;
    }
}
