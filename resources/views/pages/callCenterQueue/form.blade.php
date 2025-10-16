@extends('layouts.app')

@section('content')
<livewire:call-center-queue-form :queueUuid="$queueUuid ?? null"/>
@endsection