@extends('layouts.app')

@section('content')
<livewire:voicemail-greeting-form :voicemailId="$voicemailId ?? null"/>
@endsection
