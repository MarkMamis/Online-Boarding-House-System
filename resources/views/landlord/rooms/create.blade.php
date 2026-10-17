@extends('layouts.landlord')

@section('content')
    @include('landlord.rooms.partials.guided-form', [
        'mode' => 'create',
        'property' => $property,
        'supportsAdvanceRequirement' => $supportsAdvanceRequirement ?? false,
    ])
@endsection
