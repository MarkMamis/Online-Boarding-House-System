<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MaintenanceController extends Controller
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

        // Get rooms under maintenance for this landlord
        $maintenanceRooms = Room::with('property')
            ->where('status', 'maintenance')
            ->whereHas('property', function ($q) use ($landlordId) {
                $q->where('landlord_id', $landlordId);
            })
            ->orderBy('updated_at', 'desc')
            ->get();

        // Get all rooms for this landlord to potentially set to maintenance
        $allRooms = Room::with('property')
            ->join('properties', 'rooms.property_id', '=', 'properties.id')
            ->where('properties.landlord_id', $landlordId)
            ->orderBy('properties.name')
            ->orderBy('rooms.room_number')
            ->select('rooms.*')
            ->get();

        return view('landlord.maintenance.index', compact('maintenanceRooms', 'allRooms'));
    }

    public function setMaintenance(Request $request)
    {
        $this->ensureLandlord();
        $landlordId = Auth::id();

        $validator = Validator::make($request->all(), [
            'room_id' => 'required|exists:rooms,id',
            'reason' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $room = Room::where('id', $request->room_id)
            ->whereHas('property', function ($q) use ($landlordId) {
                $q->where('landlord_id', $landlordId);
            })
            ->firstOrFail();

        $room->update([
            'status' => 'maintenance',
            'maintenance_reason' => $request->reason,
            'maintenance_date' => now(),
        ]);

        return back()->with('success', 'Room set to maintenance mode.');
    }

    public function completeMaintenance($roomId)
    {
        $this->ensureLandlord();
        $landlordId = Auth::id();

        $room = Room::where('id', $roomId)
            ->where('status', 'maintenance')
            ->whereHas('property', function ($q) use ($landlordId) {
                $q->where('landlord_id', $landlordId);
            })
            ->firstOrFail();

        $room->update([
            'status' => 'available',
            'maintenance_reason' => null,
            'maintenance_date' => null,
        ]);

        return back()->with('success', 'Maintenance completed. Room is now available.');
    }
}
