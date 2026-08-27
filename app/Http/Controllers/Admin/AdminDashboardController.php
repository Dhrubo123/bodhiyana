<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\AdminActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminDashboardController extends Controller
{
    public function dashboard(): JsonResponse
    {
        return response()->json([
            'stats' => [
                'donors' => Donor::count(),
                'pending' => Donation::where('status', 'pending')->count(),
                'confirmed' => Donation::where('status', 'confirmed')->count(),
                'rejected' => Donation::where('status', 'rejected')->count(),
                'confirmed_amount' => Donation::where('status', 'confirmed')->sum('amount'),
            ],
            'pending' => Donation::with('purpose:id,name_bn')
                ->where('status', 'pending')->latest('submitted_at')->limit(6)->get(),
            'recent_confirmed' => Donation::with('purpose:id,name_bn')
                ->where('status', 'confirmed')->latest('confirmed_at')->limit(6)->get(),
        ]);
    }

    public function donations(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:pending,confirmed,rejected'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $query = Donation::with(['purpose:id,name_bn', 'bankAccount:id,bank_name'])
            ->latest('submitted_at');

        $query->when($validated['status'] ?? null, fn ($q, $status) => $q->where('status', $status));
        $query->when($validated['search'] ?? null, function ($q, $search) {
            $q->where(function ($inner) use ($search) {
                $inner->where('receipt_number', 'like', "%{$search}%")
                    ->orWhere('donor_name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('transaction_id', 'like', "%{$search}%");
            });
        });

        return response()->json($query->paginate(15));
    }

    public function show(Donation $donation): JsonResponse
    {
        return response()->json($donation->load(['purpose:id,name_bn,name_en', 'bankAccount']));
    }

    public function confirm(Request $request, Donation $donation): JsonResponse
    {
        $data = $request->validate(['verification_note' => ['nullable', 'string', 'max:2000']]);

        $confirmed = DB::transaction(function () use ($request, $donation, $data) {
            $locked = Donation::lockForUpdate()->findOrFail($donation->id);
            abort_unless($locked->status === 'pending', 409, 'শুধুমাত্র অপেক্ষমাণ দান নিশ্চিত করা যাবে।');
            $locked->status = 'confirmed';
            $locked->verification_note = $data['verification_note'] ?? null;
            $locked->confirmed_at = now();
            $locked->confirmed_by = $request->user()->id;
            $locked->rejected_at = null;
            $locked->rejected_by = null;
            $locked->rejection_reason = null;
            $locked->save();
            $this->logReview($request, $locked, 'donation_confirmed', 'Donation confirmed');
            return $locked;
        });

        return response()->json(['message' => 'দান সফলভাবে নিশ্চিত করা হয়েছে।', 'donation' => $confirmed->load('purpose')]);
    }

    public function reject(Request $request, Donation $donation): JsonResponse
    {
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:2000'],
            'verification_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $rejected = DB::transaction(function () use ($request, $donation, $data) {
            $locked = Donation::lockForUpdate()->findOrFail($donation->id);
            abort_unless($locked->status === 'pending', 409, 'শুধুমাত্র অপেক্ষমাণ দান প্রত্যাখ্যান করা যাবে।');
            $locked->status = 'rejected';
            $locked->verification_note = $data['verification_note'] ?? null;
            $locked->rejection_reason = $data['rejection_reason'];
            $locked->rejected_at = now();
            $locked->rejected_by = $request->user()->id;
            $locked->save();
            $this->logReview($request, $locked, 'donation_rejected', 'Donation rejected');
            return $locked;
        });

        return response()->json(['message' => 'দান প্রত্যাখ্যান করা হয়েছে।', 'donation' => $rejected->load('purpose')]);
    }

    public function screenshot(Donation $donation): BinaryFileResponse
    {
        abort_unless($donation->payment_screenshot_path && Storage::disk('local')->exists($donation->payment_screenshot_path), 404);
        return response()->file(Storage::disk('local')->path($donation->payment_screenshot_path), [
            'Cache-Control' => 'private, no-store',
            'Content-Disposition' => 'inline; filename="payment-proof"',
        ]);
    }

    private function logReview(Request $request, Donation $donation, string $action, string $description): void
    {
        AdminActivityLog::create([
            'admin_user_id' => $request->user()->id, 'action' => $action,
            'entity_type' => Donation::class, 'entity_id' => $donation->id,
            'description' => $description.' '.$donation->receipt_number, 'ip_address' => $request->ip(),
        ]);
    }
}
