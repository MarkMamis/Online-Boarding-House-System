@extends('layouts.landlord')

@section('content')
    @include('landlord.rooms.partials.guided-form', [
        'mode' => 'edit',
        'property' => $property,
        'room' => $room,
        'roomImages' => $roomImages ?? collect(),
        'supportsAdvanceRequirement' => $supportsAdvanceRequirement ?? false,
    ])
@endsection
