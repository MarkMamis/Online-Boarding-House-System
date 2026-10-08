<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Property;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    protected function ensureLandlord()
    {
        if (!Auth::check() || Auth::user()->role !== 'landlord') {
            abort(403, 'Unauthorized');
        }
    }

    public function index()
    {
        $this->ensureLandlord();
        $landlordId = Auth::id();

        // Occupancy Analytics
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
            ->get();

        $totalRooms = $properties->sum('total_rooms');
        $occupiedRooms = $properties->sum('occupied_rooms');
        $availableRooms = $properties->sum('available_rooms');
        $maintenanceRooms = $properties->sum('maintenance_rooms');

        $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0;

        // Revenue Analytics (last 30 days collected revenue from verified payments)
        $thirtyDaysAgo = now()->subDays(30);

        $monthlyRevenue = (float) \App\Models\TenantPayment::where('status', 'approved')
            ->where(function ($q) use ($thirtyDaysAgo) {
                $q->where('reviewed_at', '>=', $thirtyDaysAgo)
                  ->orWhere(function ($sq) use ($thirtyDaysAgo) {
                      $sq->whereNull('reviewed_at')->where('created_at', '>=', $thirtyDaysAgo);
                  });
            })
            ->whereHas('booking.room.property', function ($q) use ($landlordId) {
                $q->where('landlord_id', $landlordId);
            })
            ->sum('amount_due');

        // Expected monthly revenue from all active approved bookings for this landlord
        $today = now()->toDateString();
        $approvedBookings = Booking::where('status', 'approved')
            ->where('check_in', '<=', $today)
            ->where('check_out', '>', $today)
            ->whereHas('room.property', function ($q) use ($landlordId) {
                $q->where('landlord_id', $landlordId);
            })
            ->with('room')
            ->get();

        $totalExpected = (float) $approvedBookings->sum(function ($booking) {
            return (float) ($booking->room->price ?? 0);
        });

        // Booking trends (last 7 days)
        $weeklyBookings = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $count = Booking::where('status', 'approved')
                ->whereDate('created_at', $date)
                ->whereHas('room.property', function ($q) use ($landlordId) {
                    $q->where('landlord_id', $landlordId);
                })
                ->count();
            $weeklyBookings[] = [
                'date' => $date,
                'count' => $count
            ];
        }

        // Top performing properties
        $topProperties = Property::where('landlord_id', $landlordId)
            ->withCount([
                'rooms as total_rooms',
                'rooms as occupied_rooms' => function ($q) {
                    $q->where('status', 'occupied');
                },
                'rooms as booking_count' => function ($q) {
                    $q->whereHas('bookings', function ($bq) {
                        $bq->where('status', 'approved');
                    });
                }
            ])
            ->orderBy('booking_count', 'desc')
            ->limit(5)
            ->get();

        return view('landlord.analytics.index', compact(
            'properties',
            'totalRooms',
            'occupiedRooms',
            'availableRooms',
            'maintenanceRooms',
            'occupancyRate',
            'monthlyRevenue',
            'totalExpected',
            'weeklyBookings',
            'topProperties'
        ));
    }
}
