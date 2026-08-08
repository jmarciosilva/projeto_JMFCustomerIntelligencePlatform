@extends('layouts.admin')

@section('content')
    <div class="py-6">
        <livewire:marketplace.customer-journey-timeline :contact="$contact" />
    </div>
@endsection
