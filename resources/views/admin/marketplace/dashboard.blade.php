@extends('layouts.admin')

@section('content')
    <div class="py-6">
        <livewire:marketplace.dashboard :application="$application" />
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush
