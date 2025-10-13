@extends('layouts.app')

@section('content')
<livewire:call-forward-form :extensionUuid="$extensionUuid ?? null"/>
@endsection
