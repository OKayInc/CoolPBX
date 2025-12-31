@extends('layouts.app')

@section('content')
<livewire:conference-profile-form :conferenceProfile="$conferenceProfile ?? null" />
@endsection
