<?php

namespace Tests\Feature;

use App\Models\DonationPurpose;
use App\Models\Banner;
use App\Models\User;
use App\Models\WebsiteSetting;
use App\Models\PaymentSetting;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_admin_can_manage_purposes(): void
    {
        $this->actingAs(User::factory()->create());

        $purpose = $this->postJson('/api/admin/purposes', [
            'name_bn' => 'পরীক্ষামূলক দান', 'name_en' => 'Test Donation',
            'description' => null, 'sort_order' => 1, 'is_active' => true,
        ])->assertCreated()->json();

        $this->putJson('/api/admin/purposes/'.$purpose['id'], [
            'name_bn' => 'আপডেট দান', 'name_en' => 'Updated Donation',
            'description' => null, 'sort_order' => 2, 'is_active' => false,
        ])->assertOk()->assertJsonPath('name_bn', 'আপডেট দান');

        $this->deleteJson('/api/admin/purposes/'.$purpose['id'])->assertOk();
        $this->assertDatabaseMissing('donation_purposes', ['id' => $purpose['id']]);
    }

    public function test_authenticated_admin_can_update_website_content(): void
    {
        $this->actingAs(User::factory()->create());

        $this->putJson('/api/admin/website', [
            'settings' => ['bihar_name' => 'পরীক্ষা বিহার', 'address' => 'ঢাকা, বাংলাদেশ'],
        ])->assertOk()->assertJsonPath('bihar_name', 'পরীক্ষা বিহার');
    }

    public function test_admin_can_upload_site_logo_and_favicon(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $response = $this->post('/api/admin/website', [
            'settings' => [
                'bihar_name' => 'বোধিনানা মেডিটেশন সেন্টার',
                'site_title' => 'Bodhinana Meditation Centre Bangladesh',
            ],
            'logo' => UploadedFile::fake()->image('logo.png', 400, 400),
            'favicon' => UploadedFile::fake()->image('favicon.png', 64, 64),
        ])->assertOk();

        $this->assertNotNull($response->json('logo_url'));
        $this->assertNotNull($response->json('favicon_url'));
        $logoPath = WebsiteSetting::where('key', 'logo_path')->value('value');
        Storage::disk('public')->assertExists($logoPath);
        Storage::disk('public')->assertExists(WebsiteSetting::where('key', 'favicon_path')->value('value'));

        $this->get('/api/website-assets/'.$logoPath)->assertOk()->assertHeader('content-type', 'image/png');

        $this->getJson('/api/website-settings')
            ->assertOk()
            ->assertJsonPath('site_title', 'Bodhinana Meditation Centre Bangladesh');
    }

    public function test_admin_can_manage_homepage_gallery_and_public_event_ticker(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $gallery = $this->post('/api/admin/gallery', [
            'title_bn' => 'বুদ্ধ পূর্ণিমা', 'title_en' => 'Buddha Purnima', 'display_order' => 1, 'is_active' => true,
            'image' => UploadedFile::fake()->image('ceremony.jpg', 1200, 800),
        ])->assertCreated()->json();

        $this->getJson('/api/gallery')->assertOk()->assertJsonPath('0.title_bn', 'বুদ্ধ পূর্ণিমা');
        $this->get($gallery['image_url'])->assertOk()->assertHeader('content-type', 'image/jpeg');

        Event::create(['title_bn' => 'প্রবারণা পূর্ণিমা', 'event_date' => today()->addDay(), 'event_time' => '18:00', 'is_active' => true]);
        $this->getJson('/api/events')->assertOk()->assertJsonPath('0.title_bn', 'প্রবারণা পূর্ণিমা');
    }

    public function test_management_endpoints_are_protected(): void
    {
        $this->getJson('/api/admin/donors')->assertUnauthorized();
        $this->getJson('/api/admin/reports')->assertUnauthorized();
    }

    public function test_authenticated_admin_can_open_every_management_module(): void
    {
        $this->actingAs(User::factory()->create());

        foreach (['donors', 'purposes', 'events', 'banners', 'gallery', 'website', 'donation-settings', 'receipts', 'reports'] as $module) {
            $this->getJson('/api/admin/'.$module)->assertOk();
        }
    }

    public function test_admin_can_upload_a_payment_qr_code(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $setting = PaymentSetting::create(['method' => 'bkash', 'is_active' => true]);

        $response = $this->postJson('/api/admin/payment-settings/'.$setting->id, [
            'number' => '01700000000', 'account_type' => 'Merchant',
            'instructions' => 'Scan the QR code.', 'is_active' => true,
            'qr_code' => UploadedFile::fake()->image('bkash-qr.png', 600, 600),
        ])->assertOk();

        $path = $response->json('qr_code_path');
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_admin_can_manage_a_homepage_banner(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $response = $this->post('/api/admin/banners', [
            'title_bn' => 'শান্তি ও সম্প্রীতির পথে',
            'title_en' => 'A Path of Peace',
            'subtitle_bn' => 'আমাদের ধ্যান কেন্দ্রে আপনাকে স্বাগতম।',
            'button_text' => 'দান করুন',
            'button_link' => '/donate',
            'display_order' => 1,
            'is_active' => '1',
            'desktop_image' => UploadedFile::fake()->image('desktop.jpg', 1600, 600),
            'mobile_image' => UploadedFile::fake()->image('mobile.jpg', 750, 1000),
        ])->assertCreated();

        $banner = Banner::findOrFail($response->json('id'));
        Storage::disk('public')->assertExists($banner->desktop_image);
        Storage::disk('public')->assertExists($banner->mobile_image);
        $this->getJson('/api/banners')->assertOk()->assertJsonPath('0.title_en', 'A Path of Peace');

        $this->deleteJson('/api/admin/banners/'.$banner->id)->assertOk();
        $this->assertDatabaseMissing('banners', ['id' => $banner->id]);
    }

    public function test_admin_can_confirm_a_pending_donation_only_once(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);
        $donation = $this->pendingDonation('DON-2026-900001');

        $this->postJson('/api/admin/donations/'.$donation->id.'/confirm', [
            'verification_note' => 'Transaction verified.',
        ])->assertOk()->assertJsonPath('donation.status', 'confirmed');

        $this->assertDatabaseHas('donations', [
            'id' => $donation->id, 'status' => 'confirmed', 'confirmed_by' => $admin->id,
        ]);
        $this->postJson('/api/admin/donations/'.$donation->id.'/confirm')->assertConflict();
    }

    public function test_admin_can_reject_a_pending_donation_with_a_reason(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);
        $donation = $this->pendingDonation('DON-2026-900002');

        $this->postJson('/api/admin/donations/'.$donation->id.'/reject', [
            'rejection_reason' => 'Transaction could not be verified.',
        ])->assertOk()->assertJsonPath('donation.status', 'rejected');

        $this->assertDatabaseHas('donations', [
            'id' => $donation->id, 'status' => 'rejected', 'rejected_by' => $admin->id,
        ]);
    }

    public function test_confirmed_receipt_pdf_is_available_to_admin_and_donor(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);
        $donation = $this->pendingDonation('DON-2026-900003');

        $this->get('/api/admin/receipts/'.$donation->id.'/pdf')->assertNotFound();
        $this->get('/api/receipts/'.$donation->public_token.'/pdf')->assertNotFound();

        $this->postJson('/api/admin/donations/'.$donation->id.'/confirm')->assertOk();

        $this->get('/api/admin/receipts/'.$donation->id.'/pdf')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->get('/api/receipts/'.$donation->public_token.'/pdf?download=1')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    private function pendingDonation(string $receipt): Donation
    {
        $donor = Donor::create(['name' => 'Test Donor', 'mobile' => '01700000000']);
        $purpose = DonationPurpose::create(['name_bn' => 'সাধারণ দান', 'is_active' => true, 'sort_order' => 0]);
        return Donation::create([
            'receipt_number' => $receipt, 'public_token' => fake()->uuid(), 'donor_id' => $donor->id,
            'donor_name' => $donor->name, 'mobile' => $donor->mobile, 'amount' => 1000,
            'donation_purpose_id' => $purpose->id, 'payment_method' => 'bkash',
            'transaction_id' => 'TX-'.$receipt, 'status' => 'pending', 'submitted_at' => now(),
        ]);
    }
}
