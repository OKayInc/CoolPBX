@extends('layouts.app')

@section('content')
<livewire:call-broadcast-form :callBroadcastUuid="$callBroadcastUuid ?? null"/>
@endsection