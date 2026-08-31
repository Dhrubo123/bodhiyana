<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\WebsiteSetting;
use Illuminate\Http\Request;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
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
        $filename = $donation->receipt_number.'.pdf';

        $fontDirectories = (new ConfigVariables())->getDefaults()['fontDir'];
        $fontData = (new FontVariables())->getDefaults()['fontdata'];
        $fontData['nirmala'] = [
            'R' => 'Kalpurush.ttf',
            'B' => 'Kalpurush.ttf',
            // Bengali uses complex conjuncts, so OpenType shaping is required.
            'useOTL' => 0xFF,
        ];

        $pdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_top' => 10,
            'margin_right' => 16,
            'margin_bottom' => 10,
            'margin_left' => 16,
            'fontDir' => array_merge($fontDirectories, [resource_path('fonts')]),
            'fontdata' => $fontData,
            'default_font' => 'nirmala',
            'autoScriptToLang' => true,
            'autoLangToFont' => false,
        ]);

        $pdf->WriteHTML(view('receipts.donation', compact('donation', 'settings'))->render());

        return response($pdf->Output('', Destination::STRING_RETURN), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('%s; filename="%s"', $request->boolean('download') ? 'attachment' : 'inline', $filename),
        ]);
    }
}
