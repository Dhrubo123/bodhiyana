<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PaymentSetting extends Model {
    protected $fillable = ['method','number','account_type','instructions','qr_code_path','is_active'];
    protected $appends = ['qr_code_url'];
    protected function casts(): array { return ['is_active'=>'boolean']; }
    public function getQrCodeUrlAttribute(): ?string { return $this->qr_code_path ? '/storage/'.ltrim($this->qr_code_path, '/') : null; }
}
