<?php
namespace App\Services;
use App\Models\Donation;
use App\Models\Donor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DonationService {
    public function submit(array $data, ?UploadedFile $screenshot): Donation {
        return DB::transaction(function () use ($data, $screenshot) {
            $donor = Donor::firstOrCreate(['mobile' => $data['mobile']], ['name' => $data['donor_name']]);
            $donor->update(['name' => $data['donor_name']]);
            $path = $screenshot?->store('payment-screenshots', 'local');
            return Donation::create(array_merge($data, [
                'donor_id' => $donor->id, 'receipt_number' => $this->nextReceiptNumber(),
                'public_token' => (string) Str::uuid(), 'payment_screenshot_path' => $path,
                'status' => 'pending', 'submitted_at' => now(),
            ]));
        });
    }
    private function nextReceiptNumber(): string {
        $prefix = 'DON-'.now()->format('Y').'-';
        $last = Donation::where('receipt_number', 'like', $prefix.'%')->lockForUpdate()->orderByDesc('id')->value('receipt_number');
        return $prefix.str_pad((string) ((int) substr((string) $last, -6) + 1), 6, '0', STR_PAD_LEFT);
    }
}
