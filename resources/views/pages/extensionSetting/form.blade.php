@extends('layouts.app')

@section('content')
<livewire:extension-setting-form 
:extensionUuid="$extensionUuid ?? null"
:extensionSettingUuid="$extensionSettingUuid ?? null"
/>
@endsection
