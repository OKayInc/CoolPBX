@extends('layouts.app')

@section('content')
<livewire:conference-control-form :conferenceControl="$conferenceControl ?? null" />
@endsection
