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

        // Revenue Analytics (last 30 days)
        $thirtyDaysAgo = now()->subDays(30);

        $recentBookings = Booking::where('status', 'approved')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->whereHas('room.property', function ($q) use ($landlordId) {
                $q->where('landlord_id', $landlordId);
            })
            ->with('room')
            ->get();

        $monthlyRevenue = $recentBookings->sum(function ($booking) {
            return $booking->room->price * $booking->getDurationInDays();
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
            'weeklyBookings',
            'topProperties'
        ));
    }
}
