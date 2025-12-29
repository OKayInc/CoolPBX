@extends('layouts.app')

@section('content')
<livewire:conference-room-form :conferenceRoom="$conferenceRoom ?? null" :conferenceCenters="$conferenceCenters" :conferenceProfiles="$conferenceProfiles" :users="$users" />
@endsection
