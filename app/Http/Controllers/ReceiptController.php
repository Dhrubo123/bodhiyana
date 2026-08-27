<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\WebsiteSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReceiptController extends Controller
{
    public function publicPdf(Request $request, string $token): Response
    {
        $donation = Donation::where('public_token', $token)->firstOrFail();
        return $this->pdfResponse($request, $donation);
    }

    public function adminPdf(Request $request, Donation $donation): Response
    {
        return $this->pdfResponse($request, $donation);
    }

    private function pdfResponse(Request $request, Donation $donation): Response
    {
        abort_unless($donation->status === 'confirmed', 404, 'রসিদ শুধুমাত্র নিশ্চিত দানের জন্য পাওয়া যাবে।');
        $donation->load(['purpose', 'bankAccount']);
        $settings = WebsiteSetting::pluck('value', 'key');
        $pdf = Pdf::loadView('receipts.donation', compact('donation', 'settings'))->setPaper('a4', 'portrait');
        $filename = $donation->receipt_number.'.pdf';
        return $request->boolean('download') ? $pdf->download($filename) : $pdf->stream($filename);
    }
}
