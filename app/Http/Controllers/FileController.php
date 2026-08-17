<?php

namespace App\Http\Controllers;

use App\Models\LandlordDocument;
use App\Models\LandlordProfile;
use App\Models\TenantOnboarding;
use App\Models\TenantPayment;
use App\Models\User;
use App\Services\FileStorageService;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function __construct(protected FileStorageService $files)
    {
    }

    /**
     * Stream or download a stored file after authorization.
     */
    public function show(Request $request, string $path)
    {
        $path = $this->files->normalize($path);
        $user = $request->user();

        if (!$user || !$this->authorizePath($user, $path)) {
            abort(403, 'Unauthorized');
        }

        $filename = basename($path);
        $inline = !$request->boolean('download');

        return $this->files->response($path, $filename, $inline);
    }

    /**
     * Authorize access based on the standardized path prefixes and the user's role.
     */
    protected function authorizePath(User $user, string $path): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        $segments = explode('/', $path);
        $prefix = $segments[0] ?? '';

        switch ($prefix) {
            case 'landlords':
                $landlordId = (int) ($segments[1] ?? 0);
                if ($user->role !== 'landlord' || (int) $user->id !== $landlordId) {
                    return false;
                }

                // New centralized landlord document paths:
                //   landlords/{landlord_id}/documents/{type}/{filename}
                if (($segments[2] ?? '') === 'documents') {
                    return LandlordDocument::where('landlord_id', $user->id)
                        ->where('file_path', $path)
                        ->exists();
                }

                return true;

            case 'students':
                $studentId = (int) ($segments[1] ?? 0);
                return $user->role === 'student' && (int) $user->id === $studentId;

            case 'properties':
            case 'rooms':
                // Property/room listing images are not sensitive and are already
                // displayed to authenticated students/landlords across the UI.
                return in_array($user->role, ['student', 'landlord'], true);

            case 'tenant-onboardings':
                $onboardingId = (int) ($segments[1] ?? 0);
                $onboarding = TenantOnboarding::with(['booking.student', 'booking.room.property'])->find($onboardingId);
                if (!$onboarding) {
                    return false;
                }
                if ($user->role === 'student') {
                    return (int) optional($onboarding->booking)->student_id === (int) $user->id;
                }
                if ($user->role === 'landlord') {
                    return (int) optional(optional($onboarding->booking->room)->property)->landlord_id === (int) $user->id;
                }
                return false;

            case 'payments':
                $paymentId = (int) ($segments[1] ?? 0);
                $payment = TenantPayment::with(['booking.student', 'booking.room.property'])->find($paymentId);
                if (!$payment) {
                    return false;
                }
                if ($user->role === 'student') {
                    return (int) $payment->student_id === (int) $user->id;
                }
                if ($user->role === 'landlord') {
                    return (int) optional(optional($payment->booking->room)->property)->landlord_id === (int) $user->id;
                }
                return false;

            default:
                // Legacy paths without an owner id: resolve ownership from the DB.
                return $this->authorizeLegacyPath($user, $path);
        }
    }

    /**
     * Legacy local-storage paths (business_permits/, student_ids/, tenant-documents/, etc.)
     * do not encode an owner id, so look the path up in the relevant tables.
     */
    protected function authorizeLegacyPath(User $user, string $path): bool
    {
        if ($user->role === 'student') {
            $owned = User::where('id', $user->id)
                ->where(function ($q) use ($path) {
                    $q->where('profile_image_path', $path)
                        ->orWhere('school_id_path', $path)
                        ->orWhere('enrollment_proof_path', $path)
                        ->orWhere('parent_contact_photo_path', $path);
                })
                ->exists();

            if ($owned) {
                return true;
            }

            $onboarding = TenantOnboarding::with(['booking.student'])
                ->where('uploaded_documents', 'like', '%' . $path . '%')
                ->orWhere('contract_signature_path', $path)
                ->orWhere('landlord_contract_signature_path', $path)
                ->orWhere('payment_proof_path', $path)
                ->first();

            if ($onboarding && (int) optional($onboarding->booking)->student_id === (int) $user->id) {
                return true;
            }

            // A student's onboarding page may display the landlord's private
            // GCash QR. Authorize it only when the path belongs to the
            // landlord attached to one of that student's bookings.
            $canViewLandlordPaymentQr = TenantOnboarding::whereHas('booking', function ($query) use ($user, $path) {
                $query->where('student_id', $user->id)
                    ->whereHas('room.property.landlord.landlordProfile', fn ($profileQuery) =>
                        $profileQuery->where('payment_gcash_qr_path', $path)
                    );
            })->exists();

            if ($canViewLandlordPaymentQr) {
                return true;
            }

            return TenantPayment::where('payment_proof_path', $path)
                ->where('student_id', $user->id)
                ->exists();
        }

        if ($user->role === 'landlord') {
            $owned = LandlordProfile::where('user_id', $user->id)
                ->where(function ($q) use ($path) {
                    $q->where('business_permit_path', $path)
                        ->orWhere('safety_certificate_path', $path)
                        ->orWhere('payment_gcash_qr_path', $path)
                        ->orWhere('contract_signature_path', $path);
                })
                ->exists();

            // Backfilled landlord_documents records that still point at the
            // original (possibly legacy) file path.
            if (!$owned) {
                $owned = LandlordDocument::where('landlord_id', $user->id)
                    ->where('file_path', $path)
                    ->exists();
            }

            if ($owned) {
                return true;
            }

            $onboarding = TenantOnboarding::with(['booking.room.property'])
                ->where('uploaded_documents', 'like', '%' . $path . '%')
                ->orWhere('contract_signature_path', $path)
                ->orWhere('landlord_contract_signature_path', $path)
                ->orWhere('payment_proof_path', $path)
                ->first();

            if ($onboarding && (int) optional(optional($onboarding->booking->room)->property)->landlord_id === (int) $user->id) {
                return true;
            }

            return TenantPayment::where('payment_proof_path', $path)
                ->whereHas('booking.room.property', fn ($q) => $q->where('landlord_id', $user->id))
                ->exists();
        }

        return false;
    }
}
