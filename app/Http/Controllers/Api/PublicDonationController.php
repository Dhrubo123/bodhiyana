<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDonationRequest;
use App\Models\BankAccount;
use App\Models\Donation;
use App\Models\DonationPurpose;
use App\Models\PaymentSetting;
use App\Services\DonationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicDonationController extends Controller {
    public function settings(): JsonResponse { return response()->json(['payment_settings' => PaymentSetting::where('is_active', true)->get(), 'bank_accounts' => BankAccount::where('is_active', true)->orderBy('display_order')->get()]); }
    public function purposes(): JsonResponse { return response()->json(DonationPurpose::where('is_active', true)->orderBy('sort_order')->get()); }
    public function store(StoreDonationRequest $request, DonationService $service): JsonResponse { $donation = $service->submit($request->safe()->except('payment_screenshot'), $request->file('payment_screenshot')); return response()->json(['message' => 'আপনার দানের তথ্য সফলভাবে জমা হয়েছে।', 'donation' => $donation->load('purpose')], 201); }
    public function status(Request $request): JsonResponse { $data = $request->validate(['receipt_number'=>['required','string'], 'mobile'=>['required','string']]); $donation = Donation::with('purpose')->where('receipt_number',$data['receipt_number'])->where('mobile', preg_replace('/^880/', '0', preg_replace('/\D/', '', $data['mobile'])))->firstOrFail(); return response()->json(['donation'=>$donation, 'receipt_available'=>$donation->status === 'confirmed']); }
}
