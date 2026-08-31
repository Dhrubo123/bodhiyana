<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\BankAccount;
use App\Models\Banner;
use App\Models\Donation;
use App\Models\DonationPurpose;
use App\Models\Donor;
use App\Models\Event;
use App\Models\GalleryImage;
use App\Models\PaymentSetting;
use App\Models\WebsiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminManagementController extends Controller
{
    private const WEBSITE_TEXT_KEYS = [
        'bihar_name', 'site_title', 'bihar_description', 'bihar_history', 'activities',
        'address', 'contact_phone', 'email', 'facebook', 'youtube', 'google_maps',
    ];

    public function donors(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search'));
        $donors = Donor::query()
            ->when($search, fn ($q) => $q->where(fn ($inner) => $inner->where('name', 'like', "%{$search}%")->orWhere('mobile', 'like', "%{$search}%")))
            ->withCount(['donations as confirmed_donations_count' => fn ($q) => $q->where('status', 'confirmed')])
            ->withSum(['donations as confirmed_amount' => fn ($q) => $q->where('status', 'confirmed')], 'amount')
            ->withMax(['donations as last_donation_at' => fn ($q) => $q->where('status', 'confirmed')], 'confirmed_at')
            ->latest()->paginate(15);

        return response()->json($donors);
    }

    public function purposes(): JsonResponse
    {
        return response()->json(DonationPurpose::orderBy('sort_order')->get());
    }

    public function storePurpose(Request $request): JsonResponse
    {
        $purpose = DonationPurpose::create($this->purposeData($request));
        $this->log($request, 'purpose_created', $purpose, 'Donation purpose created');
        return response()->json($purpose, 201);
    }

    public function updatePurpose(Request $request, DonationPurpose $purpose): JsonResponse
    {
        $purpose->update($this->purposeData($request));
        $this->log($request, 'purpose_updated', $purpose, 'Donation purpose updated');
        return response()->json($purpose);
    }

    public function destroyPurpose(Request $request, DonationPurpose $purpose): JsonResponse
    {
        abort_if($purpose->donations()->exists(), 422, 'এই উদ্দেশ্যের সাথে দান যুক্ত আছে, তাই মুছে ফেলা যাবে না।');
        $this->log($request, 'purpose_deleted', $purpose, 'Donation purpose deleted');
        $purpose->delete();
        return response()->json(['message' => 'মুছে ফেলা হয়েছে।']);
    }

    public function events(): JsonResponse
    {
        return response()->json(Event::latest('event_date')->get());
    }

    public function banners(): JsonResponse
    {
        return response()->json(Banner::orderBy('display_order')->latest()->get());
    }

    public function gallery(): JsonResponse
    {
        return response()->json(GalleryImage::orderBy('display_order')->latest()->get());
    }

    public function storeGalleryImage(Request $request): JsonResponse
    {
        $data = $this->galleryData($request, true);
        $data['image_path'] = $request->file('image')->store('gallery', 'public');
        $image = GalleryImage::create($data);
        $this->log($request, 'gallery_image_created', $image, 'Gallery image added');
        return response()->json($image, 201);
    }

    public function updateGalleryImage(Request $request, GalleryImage $galleryImage): JsonResponse
    {
        $data = $this->galleryData($request, false);
        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($galleryImage->image_path);
            $data['image_path'] = $request->file('image')->store('gallery', 'public');
        }
        $galleryImage->update($data);
        $this->log($request, 'gallery_image_updated', $galleryImage, 'Gallery image updated');
        return response()->json($galleryImage->fresh());
    }

    public function destroyGalleryImage(Request $request, GalleryImage $galleryImage): JsonResponse
    {
        Storage::disk('public')->delete($galleryImage->image_path);
        $this->log($request, 'gallery_image_deleted', $galleryImage, 'Gallery image deleted');
        $galleryImage->delete();
        return response()->json(['message' => 'গ্যালারির ছবি মুছে ফেলা হয়েছে।']);
    }

    public function storeBanner(Request $request): JsonResponse
    {
        $data = $this->bannerData($request, true);
        $data['desktop_image'] = $request->file('desktop_image')->store('banners', 'public');
        if ($request->hasFile('mobile_image')) $data['mobile_image'] = $request->file('mobile_image')->store('banners', 'public');
        $banner = Banner::create($data);
        $this->log($request, 'banner_created', $banner, 'Website banner created');
        return response()->json($banner, 201);
    }

    public function updateBanner(Request $request, Banner $banner): JsonResponse
    {
        $data = $this->bannerData($request, false);

        if ($request->hasFile('desktop_image')) {
            Storage::disk('public')->delete($banner->desktop_image);
            $data['desktop_image'] = $request->file('desktop_image')->store('banners', 'public');
        }
        if ($request->boolean('remove_mobile_image') && $banner->mobile_image) {
            Storage::disk('public')->delete($banner->mobile_image);
            $data['mobile_image'] = null;
        }
        if ($request->hasFile('mobile_image')) {
            if ($banner->mobile_image) Storage::disk('public')->delete($banner->mobile_image);
            $data['mobile_image'] = $request->file('mobile_image')->store('banners', 'public');
        }

        $banner->update($data);
        $this->log($request, 'banner_updated', $banner, 'Website banner updated');
        return response()->json($banner->fresh());
    }

    public function destroyBanner(Request $request, Banner $banner): JsonResponse
    {
        Storage::disk('public')->delete(array_filter([$banner->desktop_image, $banner->mobile_image]));
        $this->log($request, 'banner_deleted', $banner, 'Website banner deleted');
        $banner->delete();
        return response()->json(['message' => 'ব্যানার মুছে ফেলা হয়েছে।']);
    }

    public function storeEvent(Request $request): JsonResponse
    {
        $event = Event::create($this->eventData($request));
        $this->log($request, 'event_created', $event, 'Event created');
        return response()->json($event, 201);
    }

    public function updateEvent(Request $request, Event $event): JsonResponse
    {
        $event->update($this->eventData($request));
        $this->log($request, 'event_updated', $event, 'Event updated');
        return response()->json($event);
    }

    public function destroyEvent(Request $request, Event $event): JsonResponse
    {
        $this->log($request, 'event_deleted', $event, 'Event deleted');
        $event->delete();
        return response()->json(['message' => 'ইভেন্ট মুছে ফেলা হয়েছে।']);
    }

    public function website(): JsonResponse
    {
        $settings = WebsiteSetting::pluck('value', 'key')->all();
        $settings['logo_url'] = $this->websiteAssetUrl($settings['logo_path'] ?? null);
        $settings['favicon_url'] = $this->websiteAssetUrl($settings['favicon_path'] ?? null);
        return response()->json($settings);
    }

    public function updateWebsite(Request $request): JsonResponse
    {
        $data = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable', 'string', 'max:5000'],
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:ico,png,jpg,jpeg,webp', 'max:1024'],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_favicon' => ['nullable', 'boolean'],
        ]);

        foreach ($data['settings'] as $key => $value) {
            abort_unless(in_array($key, self::WEBSITE_TEXT_KEYS, true), 422);
            WebsiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        $this->updateWebsiteAsset($request, 'logo');
        $this->updateWebsiteAsset($request, 'favicon');
        $this->log($request, 'website_updated', null, 'Website content updated');
        return $this->website();
    }

    public function donationSettings(): JsonResponse
    {
        return response()->json([
            'payment_settings' => PaymentSetting::orderBy('method')->get(),
            'bank_accounts' => BankAccount::orderBy('display_order')->get(),
        ]);
    }

    public function updatePaymentSetting(Request $request, PaymentSetting $setting): JsonResponse
    {
        $data = $request->validate([
            'number' => ['nullable', 'string', 'max:30'], 'account_type' => ['nullable', 'string', 'max:50'],
            'instructions' => ['nullable', 'string', 'max:2000'], 'is_active' => ['required', 'boolean'],
            'qr_code' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_qr' => ['nullable', 'boolean'],
        ]);
        unset($data['qr_code'], $data['remove_qr']);

        if ($request->boolean('remove_qr') && $setting->qr_code_path) {
            Storage::disk('public')->delete($setting->qr_code_path);
            $data['qr_code_path'] = null;
        }

        if ($request->hasFile('qr_code')) {
            if ($setting->qr_code_path) Storage::disk('public')->delete($setting->qr_code_path);
            $data['qr_code_path'] = $request->file('qr_code')->store('payment-qr', 'public');
        }

        $setting->update($data);
        $this->log($request, 'payment_setting_updated', $setting, strtoupper($setting->method).' setting updated');
        return response()->json($setting);
    }

    public function storeBank(Request $request): JsonResponse
    {
        $bank = BankAccount::create($this->bankData($request));
        $this->log($request, 'bank_created', $bank, 'Bank account created');
        return response()->json($bank, 201);
    }

    public function updateBank(Request $request, BankAccount $bank): JsonResponse
    {
        $bank->update($this->bankData($request));
        $this->log($request, 'bank_updated', $bank, 'Bank account updated');
        return response()->json($bank);
    }

    public function destroyBank(Request $request, BankAccount $bank): JsonResponse
    {
        abort_if($bank->donations()->exists(), 422, 'এই ব্যাংক অ্যাকাউন্টের সাথে দান যুক্ত আছে।');
        $this->log($request, 'bank_deleted', $bank, 'Bank account deleted');
        $bank->delete();
        return response()->json(['message' => 'ব্যাংক অ্যাকাউন্ট মুছে ফেলা হয়েছে।']);
    }

    public function receipts(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search'));
        return response()->json(Donation::with('purpose:id,name_bn')->where('status', 'confirmed')
            ->when($search, fn ($q) => $q->where(fn ($inner) => $inner->where('receipt_number', 'like', "%{$search}%")->orWhere('donor_name', 'like', "%{$search}%")->orWhere('mobile', 'like', "%{$search}%")))
            ->latest('confirmed_at')->paginate(15));
    }

    public function reports(Request $request): JsonResponse
    {
        $data = $request->validate(['date_from' => ['nullable', 'date'], 'date_to' => ['nullable', 'date', 'after_or_equal:date_from']]);
        $base = Donation::query()->when($data['date_from'] ?? null, fn ($q, $date) => $q->whereDate('submitted_at', '>=', $date))->when($data['date_to'] ?? null, fn ($q, $date) => $q->whereDate('submitted_at', '<=', $date));
        $confirmed = (clone $base)->where('status', 'confirmed');
        return response()->json([
            'confirmed_donors' => (clone $confirmed)->distinct('donor_id')->count('donor_id'),
            'confirmed_donations' => (clone $confirmed)->count(), 'confirmed_amount' => (clone $confirmed)->sum('amount'),
            'pending' => (clone $base)->where('status', 'pending')->count(), 'rejected' => (clone $base)->where('status', 'rejected')->count(),
            'by_purpose' => (clone $confirmed)->join('donation_purposes', 'donation_purposes.id', '=', 'donations.donation_purpose_id')->select('donation_purposes.name_bn', DB::raw('COUNT(*) as donations_count'), DB::raw('SUM(amount) as total_amount'))->groupBy('donation_purposes.id', 'donation_purposes.name_bn')->get(),
        ]);
    }

    private function purposeData(Request $request): array
    {
        return $request->validate(['name_bn' => ['required', 'string', 'max:150'], 'name_en' => ['nullable', 'string', 'max:150'], 'description' => ['nullable', 'string', 'max:1000'], 'is_active' => ['required', 'boolean'], 'sort_order' => ['required', 'integer', 'min:0']]);
    }

    private function eventData(Request $request): array
    {
        return $request->validate(['title_bn' => ['required', 'string', 'max:200'], 'title_en' => ['nullable', 'string', 'max:200'], 'description' => ['nullable', 'string', 'max:3000'], 'event_date' => ['required', 'date'], 'event_time' => ['nullable', 'date_format:H:i'], 'location' => ['nullable', 'string', 'max:250'], 'is_active' => ['required', 'boolean']]);
    }

    private function bannerData(Request $request, bool $creating): array
    {
        return $request->validate([
            'title_bn' => ['required', 'string', 'max:200'],
            'title_en' => ['nullable', 'string', 'max:200'],
            'subtitle_bn' => ['nullable', 'string', 'max:1000'],
            'subtitle_en' => ['nullable', 'string', 'max:1000'],
            'button_text' => ['nullable', 'string', 'max:100'],
            'button_link' => ['nullable', 'string', 'max:500'],
            'display_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'desktop_image' => [$creating ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'mobile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_mobile_image' => ['nullable', 'boolean'],
        ]);
    }

    private function galleryData(Request $request, bool $creating): array
    {
        return $request->validate([
            'title_bn' => ['nullable', 'string', 'max:200'],
            'title_en' => ['nullable', 'string', 'max:200'],
            'display_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'image' => [$creating ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
    }

    private function bankData(Request $request): array
    {
        return $request->validate(['bank_name' => ['required', 'string', 'max:150'], 'account_name' => ['required', 'string', 'max:150'], 'account_number' => ['required', 'string', 'max:100'], 'branch_name' => ['nullable', 'string', 'max:150'], 'routing_number' => ['nullable', 'string', 'max:100'], 'swift_code' => ['nullable', 'string', 'max:50'], 'instructions' => ['nullable', 'string', 'max:2000'], 'display_order' => ['required', 'integer', 'min:0'], 'is_active' => ['required', 'boolean']]);
    }

    private function log(Request $request, string $action, mixed $entity, string $description): void
    {
        AdminActivityLog::create(['admin_user_id' => $request->user()->id, 'action' => $action, 'entity_type' => $entity ? $entity::class : null, 'entity_id' => $entity?->id, 'description' => $description, 'ip_address' => $request->ip()]);
    }

    private function updateWebsiteAsset(Request $request, string $asset): void
    {
        $key = $asset.'_path';
        $current = WebsiteSetting::where('key', $key)->value('value');

        if ($request->boolean('remove_'.$asset) && $current) {
            Storage::disk('public')->delete($current);
            WebsiteSetting::updateOrCreate(['key' => $key], ['value' => null]);
            $current = null;
        }

        if ($request->hasFile($asset)) {
            if ($current) Storage::disk('public')->delete($current);
            WebsiteSetting::updateOrCreate([
                'key' => $key,
            ], [
                'value' => $request->file($asset)->store('website', 'public'),
            ]);
        }
    }

    private function websiteAssetUrl(?string $path): ?string
    {
        if (! $path || ! Storage::disk('public')->exists($path)) return null;
        return '/api/website-assets/'.ltrim($path, '/').'?v='.Storage::disk('public')->lastModified($path);
    }
}
