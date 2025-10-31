@extends('layouts.app')

@section('content')
<livewire:voicemail-form :voicemailUuid="$voicemailUuid ?? null"/>
@endsection
