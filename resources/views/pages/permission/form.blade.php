@extends('layouts.app')

@section('content')
<livewire:permission-form :permissionUuid="$permissionUuid ?? null"/>
@endsection
