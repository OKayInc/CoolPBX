@extends('layouts.app')

@section('content')
<livewire:ringgroup-form :ringgroupUuid="$ringgroupUuid ?? null"/>
@endsection
