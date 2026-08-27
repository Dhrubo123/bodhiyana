<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Donation extends Model {
    protected $fillable = ['receipt_number','public_token','donor_id','donor_name','mobile','amount','donation_purpose_id','payment_method','bank_account_id','transaction_id','payment_screenshot_path','status','note','submitted_at'];
    protected function casts(): array { return ['amount'=>'decimal:2','submitted_at'=>'datetime','confirmed_at'=>'datetime','rejected_at'=>'datetime']; }
    public function donor(): BelongsTo { return $this->belongsTo(Donor::class); }
    public function purpose(): BelongsTo { return $this->belongsTo(DonationPurpose::class, 'donation_purpose_id'); }
    public function bankAccount(): BelongsTo { return $this->belongsTo(BankAccount::class); }
}
