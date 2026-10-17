@extends('layouts.landlord')

@section('content')
    @include('landlord.properties.partials.guided-form', [
        'mode' => 'create',
        'property' => $draftProperty ?? null,
    ])
@endsection
