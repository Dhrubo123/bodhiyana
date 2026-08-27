<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\BankAccount;
use App\Models\DonationPurpose;
use App\Models\PaymentSetting;
use App\Models\WebsiteSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.test'],
            ['name' => 'Bihar Administrator', 'password' => Hash::make('change-this-password')]
        );
        foreach ([['সাধারণ দান', 'General Donation'], ['সংঘ দান', 'Sangha Dana'], ['বিহার উন্নয়ন', 'Bihar Development'], ['খাদ্য দান', 'Food Dana']] as $i => [$bn, $en]) DonationPurpose::updateOrCreate(['name_bn'=>$bn], ['name_en'=>$en, 'sort_order'=>$i]);
        BankAccount::updateOrCreate(['account_number'=>'000000000000'], ['bank_name'=>'Sample Bank PLC','account_name'=>'Bihar Donation Account','branch_name'=>'Main Branch','instructions'=>'Replace this sample account in the admin dashboard.','is_active'=>false]);
        foreach (['bkash','nagad'] as $method) PaymentSetting::updateOrCreate(['method'=>$method], ['account_type'=>'Personal','instructions'=>'Configure payment details in the admin dashboard.','is_active'=>false]);
        foreach (['bihar_name'=>'আপনার বৌদ্ধ বিহার','contact_phone'=>'','address'=>'বাংলাদেশ'] as $key => $value) WebsiteSetting::updateOrCreate(['key'=>$key], ['value'=>$value]);
    }
}
