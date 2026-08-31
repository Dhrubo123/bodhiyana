<!doctype html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <title>{{ $donation->receipt_number }}</title>
    <style>
        @font-face { font-family: Nirmala; src: url("{{ str_replace('\\', '/', resource_path('fonts/Nirmala.ttf')) }}") format('truetype'); font-weight: 400; font-style: normal; }
        @font-face { font-family: Nirmala; src: url("{{ str_replace('\\', '/', resource_path('fonts/NirmalaB.ttf')) }}") format('truetype'); font-weight: 700; font-style: normal; }
        @page { margin: 10mm 16mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #263c32; font-family: Nirmala, DejaVu Sans, sans-serif; font-size: 12px; line-height: 1.45; word-break: normal; overflow-wrap: normal; }
        .receipt { border: 1px solid #d8dfda; padding: 12px 27px 8px; }
        .top { text-align: center; border-bottom: 2px solid #2b654f; padding-bottom: 9px; }
        .wheel { color: #b77825; font-family: dejavusans, sans-serif; font-size: 25px; line-height: 1; }
        .centre { margin: 5px 0 3px; color: #164a39; font-family: DejaVu Sans, sans-serif; font-size: 20px; font-weight: bold; letter-spacing: .3px; }
        .address { margin: 2px 0; color: #65746c; font-size: 10px; }
        .title { margin: 10px 0 3px; text-align: center; font-size: 19px; font-weight: bold; }
        .subtitle { margin: 0 0 9px; color: #738078; text-align: center; }
        .meta { width: 100%; margin-bottom: 9px; border-collapse: collapse; }
        .meta td { width: 50%; padding: 6px 10px; border: 1px solid #e0e5e1; }
        .label { display: block; margin-bottom: 3px; color: #7e8b83; font-size: 9px; }
        .value { color: #253e33; font-weight: bold; }
        .section { margin-top: 9px; }
        .section h3 { margin: 0 0 6px; padding-bottom: 4px; border-bottom: 1px solid #dfe5e0; color: #2b604a; font-size: 12px; }
        .section h3 small { margin-left: 7px; color: #728078; font-family: DejaVu Sans, sans-serif; font-size: 9px; font-weight: normal; }
        .details { width: 100%; border-collapse: collapse; }
        .details td { padding: 4px 8px; border-bottom: 1px solid #eef1ef; vertical-align: top; }
        .details td:first-child { width: 40%; color: #75827a; }
        .bn-label { color: #65756c; font-family: Nirmala, sans-serif; font-size: 11px; line-height: 1.65; white-space: nowrap; word-break: keep-all; }
        .en-label { color: #87938c; font-family: DejaVu Sans, sans-serif; font-size: 8px; line-height: 1.25; }
        .bn-value { font-family: Nirmala, sans-serif; font-size: 12px; line-height: 1.65; word-break: keep-all; }
        .amount { margin: 10px 0; padding: 9px; border: 1px solid #dfd5bb; background: #faf5e8; color: #23523e; text-align: center; font-size: 21px; font-weight: bold; }
        .confirmed { display: inline-block; padding: 4px 10px; border-radius: 12px; background: #e5f2e8; color: #34734a; font-size: 10px; font-weight: bold; }
        .thanks { margin-top: 9px; padding-top: 7px; border-top: 1px solid #dfe5e0; text-align: center; }
        .thanks strong { color: #9c651e; font-size: 17px; }
        .thanks p { margin: 4px 0; color: #66766d; line-height: 1.4; }
        .footer { margin-top: 4px; color: #87938c; text-align: center; font-size: 8px; }
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

    <h1 class="title">Donation Receipt / <span class="bn-value">দানের রসিদ</span></h1>
    <p class="subtitle">Official acknowledgement of confirmed donation</p>

    <table class="meta"><tr>
        <td><span class="label"><span class="bn-label">রসিদ নম্বর</span><br><span class="en-label">Receipt Number</span></span><br><span class="value">{{ $donation->receipt_number }}</span></td>
        <td><span class="label"><span class="bn-label">অবস্থা</span><br><span class="en-label">Status</span></span><br><span class="confirmed">CONFIRMED</span></td>
    </tr></table>

    <div class="section"><h3><span class="bn-value">দাতার তথ্য</span><small>Donor Information</small></h3><table class="details">
        <tr><td><span class="bn-label">দাতার নাম</span><br><span class="en-label">Donor Name</span></td><td><strong class="bn-value">{{ $donation->donor_name }}</strong></td></tr>
        <tr><td><span class="bn-label">মোবাইল নম্বর</span><br><span class="en-label">Mobile</span></td><td>{{ $donation->mobile }}</td></tr>
    </table></div>

    <div class="section"><h3><span class="bn-value">দানের তথ্য</span><small>Donation Information</small></h3><table class="details">
        <tr><td><span class="bn-label">দানের উদ্দেশ্য</span><br><span class="en-label">Purpose</span></td><td><span class="bn-value">{{ $donation->purpose?->name_bn }}</span>@if($donation->purpose?->name_en) / {{ $donation->purpose->name_en }}@endif</td></tr>
        <tr><td><span class="bn-label">পেমেন্ট পদ্ধতি</span><br><span class="en-label">Payment Method</span></td><td>{{ strtoupper($donation->payment_method) }}</td></tr>
        @if($donation->bankAccount)<tr><td><span class="bn-label">ব্যাংক</span><br><span class="en-label">Bank</span></td><td>{{ $donation->bankAccount->bank_name }} - {{ $donation->bankAccount->account_name }} ({{ $donation->bankAccount->account_number }})</td></tr>@endif
        @if($donation->transaction_id)<tr><td><span class="bn-label">লেনদেন আইডি</span><br><span class="en-label">Transaction ID</span></td><td>{{ $donation->transaction_id }}</td></tr>@endif
        <tr><td><span class="bn-label">দানের তারিখ</span><br><span class="en-label">Donation Date</span></td><td>{{ $donation->submitted_at?->format('d M Y, h:i A') }}</td></tr>
        <tr><td><span class="bn-label">নিশ্চিত হওয়ার তারিখ</span><br><span class="en-label">Confirmation Date</span></td><td>{{ $donation->confirmed_at?->format('d M Y, h:i A') }}</td></tr>
    </table></div>

    <div class="amount">৳ {{ number_format((float) $donation->amount, 2) }}</div>
    <div class="thanks"><strong>সাধু! সাধু! সাধু!</strong><p>আপনার মূল্যবান দানের জন্য আন্তরিক কৃতজ্ঞতা।<br>Your generous contribution supports our religious, educational, social and service activities.</p></div>
    <div class="footer">This receipt was generated electronically and is valid without a signature.</div>
</div>
</body>
</html>
