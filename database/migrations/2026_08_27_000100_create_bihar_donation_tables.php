<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('donors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mobile', 15)->unique();
            $table->timestamps();
        });
        Schema::create('donation_purposes', function (Blueprint $table) {
            $table->id(); $table->string('name_bn'); $table->string('name_en')->nullable();
            $table->text('description')->nullable(); $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0); $table->timestamps();
        });
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id(); $table->string('bank_name'); $table->string('account_name');
            $table->string('account_number'); $table->string('branch_name')->nullable();
            $table->string('routing_number')->nullable(); $table->string('swift_code')->nullable();
            $table->string('logo')->nullable(); $table->text('instructions')->nullable();
            $table->unsignedInteger('display_order')->default(0); $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id(); $table->string('method')->unique(); $table->string('number')->nullable();
            $table->string('account_type')->nullable(); $table->text('instructions')->nullable();
            $table->string('qr_code_path')->nullable(); $table->boolean('is_active')->default(false); $table->timestamps();
        });
        Schema::create('website_settings', function (Blueprint $table) {
            $table->id(); $table->string('key')->unique(); $table->text('value')->nullable(); $table->timestamps();
        });
        Schema::create('donations', function (Blueprint $table) {
            $table->id(); $table->string('receipt_number')->unique(); $table->uuid('public_token')->unique();
            $table->foreignId('donor_id')->constrained(); $table->string('donor_name'); $table->string('mobile', 15);
            $table->decimal('amount', 12, 2); $table->foreignId('donation_purpose_id')->constrained();
            $table->enum('payment_method', ['bkash','nagad','bank']); $table->foreignId('bank_account_id')->nullable()->constrained();
            $table->string('transaction_id')->nullable(); $table->string('payment_screenshot_path')->nullable();
            $table->enum('status', ['pending','confirmed','rejected'])->default('pending'); $table->text('note')->nullable();
            $table->text('verification_note')->nullable(); $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at'); $table->timestamp('confirmed_at')->nullable(); $table->timestamp('rejected_at')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users'); $table->foreignId('rejected_by')->nullable()->constrained('users');
            $table->timestamps(); $table->index(['payment_method','transaction_id']); $table->index(['mobile','status']);
        });
        Schema::create('banners', function (Blueprint $table) { $table->id(); $table->string('title_bn'); $table->string('title_en')->nullable(); $table->text('subtitle_bn')->nullable(); $table->text('subtitle_en')->nullable(); $table->string('desktop_image'); $table->string('mobile_image')->nullable(); $table->string('button_text')->nullable(); $table->string('button_link')->nullable(); $table->unsignedInteger('display_order')->default(0); $table->boolean('is_active')->default(true); $table->date('start_date')->nullable(); $table->date('end_date')->nullable(); $table->timestamps(); });
        Schema::create('events', function (Blueprint $table) { $table->id(); $table->string('title_bn'); $table->string('title_en')->nullable(); $table->text('description')->nullable(); $table->date('event_date'); $table->time('event_time')->nullable(); $table->string('location')->nullable(); $table->string('image')->nullable(); $table->boolean('is_active')->default(true); $table->timestamps(); });
        Schema::create('admin_activity_logs', function (Blueprint $table) { $table->id(); $table->foreignId('admin_user_id')->nullable()->constrained('users')->nullOnDelete(); $table->string('action'); $table->string('entity_type')->nullable(); $table->unsignedBigInteger('entity_id')->nullable(); $table->text('description')->nullable(); $table->ipAddress('ip_address')->nullable(); $table->timestamps(); });
    }
    public function down(): void { Schema::dropIfExists('admin_activity_logs'); Schema::dropIfExists('events'); Schema::dropIfExists('banners'); Schema::dropIfExists('donations'); Schema::dropIfExists('website_settings'); Schema::dropIfExists('payment_settings'); Schema::dropIfExists('bank_accounts'); Schema::dropIfExists('donation_purposes'); Schema::dropIfExists('donors'); }
};
