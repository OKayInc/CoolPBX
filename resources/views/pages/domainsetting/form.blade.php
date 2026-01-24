@extends('layouts.app')

@section('content')
<livewire:domain-setting-form 
    :domainSettingUuid="$domainSettingUuid ?? null" 
    :domainUuid="$domainUuid" 
/>
@endsection
