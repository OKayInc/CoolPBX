@extends('layouts.app')

@section('content')
<livewire:ringgroup-form :ringGroupUuid="$ringGroupUuid ?? null"/>
@endsection
