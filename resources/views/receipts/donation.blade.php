<!doctype html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <title>{{ $donation->receipt_number }}</title>
    <style>
        @font-face { font-family: Nirmala; src: url("{{ str_replace('\\', '/', resource_path('fonts/Nirmala.ttf')) }}") format('truetype'); font-weight: 400; font-style: normal; }
        @font-face { font-family: Nirmala; src: url("{{ str_replace('\\', '/', resource_path('fonts/NirmalaB.ttf')) }}") format('truetype'); font-weight: 700; font-style: normal; }
        @page { margin: 15mm 16mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #263c32; font-family: Nirmala, DejaVu Sans, sans-serif; font-size: 12px; }
        .receipt { border: 1px solid #d8dfda; padding: 19px 27px 17px; }
        .top { text-align: center; border-bottom: 2px solid #2b654f; padding-bottom: 13px; }
        .wheel { color: #b77825; font-size: 30px; line-height: 1; }
        .centre { margin: 8px 0 3px; color: #164a39; font-family: DejaVu Sans, sans-serif; font-size: 20px; font-weight: bold; letter-spacing: .3px; }
        .address { margin: 2px 0; color: #65746c; font-size: 10px; }
        .title { margin: 14px 0 3px; text-align: center; font-size: 19px; font-weight: bold; }
        .subtitle { margin: 0 0 14px; color: #738078; text-align: center; }
        .meta { width: 100%; margin-bottom: 12px; border-collapse: collapse; }
        .meta td { width: 50%; padding: 6px 10px; border: 1px solid #e0e5e1; }
        .label { display: block; margin-bottom: 3px; color: #7e8b83; font-size: 9px; }
        .value { color: #253e33; font-weight: bold; }
        .section { margin-top: 11px; }
        .section h3 { margin: 0 0 8px; padding-bottom: 6px; border-bottom: 1px solid #dfe5e0; color: #2b604a; font-size: 12px; }
        .details { width: 100%; border-collapse: collapse; }
        .details td { padding: 5px 8px; border-bottom: 1px solid #eef1ef; vertical-align: top; }
        .details td:first-child { width: 37%; color: #75827a; }
        .amount { margin: 14px 0; padding: 10px; border: 1px solid #dfd5bb; background: #faf5e8; color: #23523e; text-align: center; font-size: 21px; font-weight: bold; }
        .confirmed { display: inline-block; padding: 4px 10px; border-radius: 12px; background: #e5f2e8; color: #34734a; font-size: 10px; font-weight: bold; }
        .thanks { margin-top: 14px; padding-top: 11px; border-top: 1px solid #dfe5e0; text-align: center; }
        .thanks strong { color: #9c651e; font-size: 17px; }
        .thanks p { margin: 7px 0; color: #66766d; line-height: 1.7; }
        .footer { margin-top: 11px; color: #87938c; text-align: center; font-size: 8px; }
    </style>
</head>
<body>
<div class="receipt">
    <div class="top">
        <div class="wheel">☸</div>
        <div class="centre">Bodhinana Meditation Centre Bangladesh</div>
        <p class="address">{{ $settings['address'] ?? 'Bangladesh' }}</p>
        @if(!empty($settings['contact_phone']) || !empty($settings['email']))
            <p class="address">{{ $settings['contact_phone'] ?? '' }} {{ !empty($settings['contact_phone']) && !empty($settings['email']) ? ' | ' : '' }} {{ $settings['email'] ?? '' }}</p>
        @endif
    </div>

    <h1 class="title">Donation Receipt / দানের রসিদ</h1>
    <p class="subtitle">Official acknowledgement of confirmed donation</p>

    <table class="meta"><tr>
        <td><span class="label">রসিদ নম্বর / Receipt Number</span><span class="value">{{ $donation->receipt_number }}</span></td>
        <td><span class="label">অবস্থা / Status</span><span class="confirmed">CONFIRMED</span></td>
    </tr></table>

    <div class="section"><h3>দাতার তথ্য / Donor Information</h3><table class="details">
        <tr><td>দাতার নাম / Donor Name</td><td><strong>{{ $donation->donor_name }}</strong></td></tr>
        <tr><td>মোবাইল নম্বর / Mobile</td><td>{{ $donation->mobile }}</td></tr>
    </table></div>

    <div class="section"><h3>দানের তথ্য / Donation Information</h3><table class="details">
        <tr><td>দানের উদ্দেশ্য / Purpose</td><td>{{ $donation->purpose?->name_bn }}@if($donation->purpose?->name_en) / {{ $donation->purpose->name_en }}@endif</td></tr>
        <tr><td>পেমেন্ট পদ্ধতি / Payment Method</td><td>{{ strtoupper($donation->payment_method) }}</td></tr>
        @if($donation->bankAccount)<tr><td>ব্যাংক / Bank</td><td>{{ $donation->bankAccount->bank_name }} - {{ $donation->bankAccount->account_name }} ({{ $donation->bankAccount->account_number }})</td></tr>@endif
        @if($donation->transaction_id)<tr><td>লেনদেন আইডি / Transaction ID</td><td>{{ $donation->transaction_id }}</td></tr>@endif
        <tr><td>দানের তারিখ / Donation Date</td><td>{{ $donation->submitted_at?->format('d M Y, h:i A') }}</td></tr>
        <tr><td>নিশ্চিত হওয়ার তারিখ / Confirmation Date</td><td>{{ $donation->confirmed_at?->format('d M Y, h:i A') }}</td></tr>
    </table></div>

    <div class="amount">৳ {{ number_format((float) $donation->amount, 2) }}</div>
    <div class="thanks"><strong>সাধু! সাধু! সাধু!</strong><p>আপনার মূল্যবান দানের জন্য আন্তরিক কৃতজ্ঞতা।<br>Your generous contribution supports our religious, educational, social and service activities.</p></div>
    <div class="footer">This receipt was generated electronically and is valid without a signature.</div>
</div>
</body>
</html>
