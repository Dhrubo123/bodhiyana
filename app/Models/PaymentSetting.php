<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PaymentSetting extends Model { protected $fillable = ['method','number','account_type','instructions','qr_code_path','is_active']; protected function casts(): array { return ['is_active'=>'boolean']; } }
