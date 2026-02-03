@extends('layouts.app')

@section('content')
<livewire:conference-form :conference="$conference ?? null" :conferenceProfiles="$conferenceProfiles" />
@endsection
