@extends('layouts.app')

@section('content')
    <livewire:billing-deal-form :billingDeal="$billingDeal ?? null" />
@endsection
