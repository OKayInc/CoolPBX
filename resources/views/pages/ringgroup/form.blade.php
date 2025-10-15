@extends('layouts.app')

@section('content')
<livewire:ring-group-form :ringGroupUuid="$ringGroupUuid ?? null"/>
@endsection
