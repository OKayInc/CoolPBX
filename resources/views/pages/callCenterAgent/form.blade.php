@extends('layouts.app')

@section('content')
<livewire:call-center-agent-form :agentUuid="$agentUuid ?? null"/>
@endsection