@extends('layouts.app')

@section('content')
<livewire:ivr-menu-form :ivrMenu="$ivrMenu ?? null" :ivrMenus="$ivrMenus ?? []" :languagePaths="$languagePaths ?? []" />
@endsection
