@extends('layouts.landlord')

@section('content')
    @include('landlord.properties.partials.guided-form', [
        'mode' => 'edit',
        'property' => $property,
    ])
@endsection
