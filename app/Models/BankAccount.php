<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class BankAccount extends Model { protected $fillable = ['bank_name','account_name','account_number','branch_name','routing_number','swift_code','logo','instructions','display_order','is_active']; protected function casts(): array { return ['is_active'=>'boolean']; } public function donations(): HasMany { return $this->hasMany(Donation::class); } }
