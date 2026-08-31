<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDonationRequest;
use App\Models\BankAccount;
use App\Models\Banner;
use App\Models\Donation;
use App\Models\DonationPurpose;
use App\Models\Event;
use App\Models\GalleryImage;
use App\Models\PaymentSetting;
use App\Models\WebsiteSetting;
use App\Services\DonationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicDonationController extends Controller {
    public function banners(): JsonResponse { return response()->json(Banner::where('is_active',true)->where(fn($q)=>$q->whereNull('start_date')->orWhereDate('start_date','<=',today()))->where(fn($q)=>$q->whereNull('end_date')->orWhereDate('end_date','>=',today()))->orderBy('display_order')->get()); }
    public function events(): JsonResponse { return response()->json(Event::where('is_active', true)->whereDate('event_date', '>=', today())->orderBy('event_date')->orderBy('event_time')->take(10)->get()); }
    public function gallery(): JsonResponse { return response()->json(GalleryImage::where('is_active', true)->orderBy('display_order')->latest()->get()); }
    public function galleryAsset(string $path): StreamedResponse { abort_unless(str_starts_with($path, 'gallery/') && Storage::disk('public')->exists($path), 404); return Storage::disk('public')->response($path, null, ['Cache-Control' => 'public, max-age=31536000, immutable']); }
    public function websiteSettings(): JsonResponse { $settings = WebsiteSetting::pluck('value', 'key')->all(); return response()->json(['bihar_name'=>$settings['bihar_name']??'আপনার বৌদ্ধ বিহার', 'site_title'=>$settings['site_title']??$settings['bihar_name']??'বৌদ্ধ বিহার | দান ব্যবস্থাপনা', 'whatsapp_number'=>$settings['whatsapp_number']??null, 'logo_url'=>$this->websiteAssetUrl($settings['logo_path']??null), 'favicon_url'=>$this->websiteAssetUrl($settings['favicon_path']??null)])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0'); }
    public function websiteAsset(string $path): StreamedResponse { abort_unless(str_starts_with($path, 'website/') && Storage::disk('public')->exists($path), 404); return Storage::disk('public')->response($path, null, ['Cache-Control' => 'public, max-age=31536000, immutable']); }
    public function settings(): JsonResponse { return response()->json(['payment_settings' => PaymentSetting::where('is_active', true)->get(), 'bank_accounts' => BankAccount::where('is_active', true)->orderBy('display_order')->get()]); }
    public function purposes(): JsonResponse { return response()->json(DonationPurpose::where('is_active', true)->orderBy('sort_order')->get()); }
    public function store(StoreDonationRequest $request, DonationService $service): JsonResponse { $donation = $service->submit($request->safe()->except('payment_screenshot'), $request->file('payment_screenshot')); return response()->json(['message' => 'আপনার দানের তথ্য সফলভাবে জমা হয়েছে।', 'donation' => $donation->load('purpose')], 201); }
    public function status(Request $request): JsonResponse { $data = $request->validate(['receipt_number'=>['required','string'], 'mobile'=>['required','string']]); $donation = Donation::with('purpose')->where('receipt_number',$data['receipt_number'])->where('mobile', preg_replace('/^880/', '0', preg_replace('/\D/', '', $data['mobile'])))->firstOrFail(); return response()->json(['donation'=>$donation, 'receipt_available'=>$donation->status === 'confirmed']); }
    private function websiteAssetUrl(?string $path): ?string { if (! $path || ! Storage::disk('public')->exists($path)) return null; return '/api/website-assets/'.ltrim($path, '/').'?v='.Storage::disk('public')->lastModified($path); }
}
