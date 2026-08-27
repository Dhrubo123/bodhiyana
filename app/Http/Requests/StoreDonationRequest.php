<?php
namespace App\Http\Requests;
use App\Models\Donation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDonationRequest extends FormRequest {
    public function authorize(): bool { return true; }
    protected function prepareForValidation(): void { $this->merge(['mobile' => preg_replace('/^880/', '0', preg_replace('/\D/', '', (string) $this->mobile))]); }
    public function rules(): array {
        $method = $this->input('payment_method');
        return [
            'donor_name' => ['required','string','max:120'], 'mobile' => ['required','regex:/^01[3-9]\d{8}$/'],
            'amount' => ['required','numeric','min:1'], 'donation_purpose_id' => ['required','exists:donation_purposes,id'],
            'payment_method' => ['required', Rule::in(['bkash','nagad','bank'])],
            'bank_account_id' => [Rule::requiredIf($method === 'bank'), 'nullable','exists:bank_accounts,id'],
            'transaction_id' => [Rule::requiredIf(in_array($method, ['bkash','nagad'])), 'nullable','string','max:100', Rule::unique('donations')->where(fn ($q) => $q->where('payment_method', $method))],
            'payment_screenshot' => [Rule::requiredIf($method === 'bank'), 'nullable','file','mimes:jpg,jpeg,png,webp','max:5120'],
            'note' => ['nullable','string','max:1000'],
        ];
    }
    public function messages(): array { return ['mobile.regex' => 'সঠিক বাংলাদেশি মোবাইল নম্বর দিন।', 'transaction_id.unique' => 'এই Transaction ID ইতিমধ্যে জমা দেওয়া হয়েছে। অনুগ্রহ করে Transaction ID যাচাই করুন।']; }
}
