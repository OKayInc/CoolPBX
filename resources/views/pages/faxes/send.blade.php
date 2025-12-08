@extends('layouts.app')

@section('content')
<livewire:fax-send :fax="$fax" :contacts="$contacts"/>
@endsection
