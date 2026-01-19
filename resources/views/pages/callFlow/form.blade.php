@extends('layouts.app')

@section('content')
<livewire:call-flow-form :callFlowUuid="$callFlowUuid ?? null"/>
@endsection
