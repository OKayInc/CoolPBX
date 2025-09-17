@extends('layouts.app')

@section('content')
<livewire:group-permission-form :groupUuid="$groupUuid ?? null"/>
@endsection
