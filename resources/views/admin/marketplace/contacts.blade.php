@extends('layouts.admin')

@section('content')
    <div class="py-6">
        <livewire:marketplace.contacts-list :tenant="$tenant" />
    </div>
@endsection
