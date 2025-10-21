@extends('layouts.app')

@section('content')
<livewire:time-condition-form :dialplanUuid="$dialplanUuid ?? null"/>
@endsection
