<?php

namespace App\Http\Controllers;

use App\Models\LandlordDocument;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use App\Services\DocumentExpirationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LandlordMonitoringController extends Controller
{
    public function __construct(
        protected DocumentExpirationService $expiration,
    ) {
    }

    protected function ensureLandlord()
    {
        if (!Auth::check() || Auth::user()->role !== 'landlord') {
            abort(403, 'Unauthorized');
        }
    }

    /**
     * Read-only operational overview of properties and room occupancy.
     */
    public function properties(Request $request)
    {
        $this->ensureLandlord();
        $landlordId = Auth::id();

        $properties = Property::where('landlord_id', $landlordId)
            ->withCount([
                'rooms as total_rooms',
                'rooms as occupied_rooms' => function ($q) {
                    $q->where('status', 'occupied');
                },
                'rooms as available_rooms' => function ($q) {
                    $q->where('status', 'available');
                },
                'rooms as maintenance_rooms' => function ($q) {
                    $q->where('status', 'maintenance');
                }
            ])
            ->with(['rooms' => function ($q) {
                $q->with(['bookings' => function ($bq) {
                    $bq->where('status', 'approved')
                        ->where('check_in', '<=', now()->toDateString())
                        ->where('check_out', '>', now()->toDateString())
                        ->with('student');
                }])->orderBy('room_number');
            }])
            ->orderBy('name')
            ->get();

        // Hydrate active occupants on each room
        foreach ($properties as $property) {
            foreach ($property->rooms as $room) {
                $activeStudents = $room->bookings
                    ->filter(fn($b) => $b->status === 'approved' && $b->student !== null)
                    ->map(fn($b) => $b->student)
                    ->values();

                $room->active_tenants = $activeStudents;
                $currentBooking = $room->bookings->first();
                $room->current_tenant = $activeStudents->first() ?? ($currentBooking ? $currentBooking->student : null);
            }
        }

        $totalProperties = $properties->count();
        $operationalProperties = $properties->where('approval_status', 'approved')->count();
        $totalRooms = $properties->sum('total_rooms');
        $occupiedRooms = $properties->sum('occupied_rooms');
        $availableRooms = $properties->sum('available_rooms');
        $maintenanceRooms = $properties->sum('maintenance_rooms');
        $totalAvailableSlots = $properties->sum(function ($p) {
            return $p->rooms->sum('slots_available');
        });

        $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0;

        return view('landlord.monitoring.properties', compact(
            'properties',
            'totalProperties',
            'operationalProperties',
            'totalRooms',
            'occupiedRooms',
            'availableRooms',
            'maintenanceRooms',
            'totalAvailableSlots',
            'occupancyRate'
        ));
    }

    /**
     * Read-only operational overview of accreditation and document compliance.
     */
    public function compliance(Request $request)
    {
        $this->ensureLandlord();
        /** @var User $user */
        $user = Auth::user();
        $user->loadMissing('landlordProfile');

        $types = LandlordDocument::types();
        $records = $user->landlordDocuments()
            ->current()
            ->orderByDesc('id')
            ->get()
            ->keyBy('document_type');

        $documents = [];
        $approvedCount = 0;
        $expiringSoonCount = 0;
        $expiredCount = 0;
        $pendingCount = 0;
        $missingCount = 0;

        foreach ($types as $type => $label) {
            $record = $records->get($type);
            $legacyPath = null;
            $legacyStatus = null;

            if (!$record) {
                if ($type === LandlordDocument::TYPE_BUSINESS_PERMIT) {
                    $legacyPath = optional($user->landlordProfile)->business_permit_path;
                    $legacyStatus = optional($user->landlordProfile)->business_permit_status;
                } elseif ($type === LandlordDocument::TYPE_SAFETY_CERTIFICATE) {
                    $legacyPath = optional($user->landlordProfile)->safety_certificate_path;
                    $legacyStatus = optional($user->landlordProfile)->safety_certificate_status;
                }
            }

            $hasFile = $record ? filled($record->file_path) : filled($legacyPath);
            $verificationStatus = $record ? $record->verification_status : (filled($legacyStatus) ? $legacyStatus : 'not_submitted');
            $expirationInfo = $record && $record->expiration_date ? $this->expiration->expirationInfo($record->expiration_date) : null;

            if ($verificationStatus === 'approved') {
                $approvedCount++;
                if ($expirationInfo) {
                    if ($expirationInfo['status'] === 'expired') {
                        $expiredCount++;
                    } elseif ($expirationInfo['status'] === 'expiring_soon') {
                        $expiringSoonCount++;
                    }
                }
            } elseif ($verificationStatus === 'pending') {
                $pendingCount++;
            } elseif (!$hasFile || $verificationStatus === 'not_submitted') {
                $missingCount++;
            }

            $documents[$type] = [
                'type' => $type,
                'label' => $label,
                'record' => $record,
                'has_file' => $hasFile,
                'verification_status' => $verificationStatus,
                'expiration' => $expirationInfo,
                'notes' => $record?->admin_notes,
            ];
        }

        $accreditationStatus = optional($user->landlordProfile)->business_permit_status ?? 'pending';

        return view('landlord.monitoring.compliance', compact(
            'user',
            'documents',
            'accreditationStatus',
            'approvedCount',
            'expiringSoonCount',
            'expiredCount',
            'pendingCount',
            'missingCount'
        ));
    }
}
