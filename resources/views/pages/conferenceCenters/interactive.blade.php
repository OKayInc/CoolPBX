@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="mt-3 card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="fas fa-layer-group mr-2"></i> {{ __('Interactive Conference') }}
                </h3>

                <div class="card-tools">
                    <div class="d-flex gap-2 " role="group" aria-label="Interactive Conference actions">

                    <img src="{{ asset('assets/icons/' . (($data['head']['recording'] ?? '') ? 'recording.png' : 'not_recording.png')) }}" width="16" height="16" alt="">

                        @can('conference_interactive_lock')
                            @if(!empty($locked) && $locked == 'true')
                                <button
                                    type="button"
                                    class="btn btn-sm btn-primary"
                                    wire:click="runCommand(@js([
                                        'cmd' => 'conference',
                                        'name' => $data['head']['conference_name'],
                                        'data' => 'unlock',
                                    ]))"
                                >
                                    <i class="fa-solid fa-unlock"></i> {{__('Unlock')}}
                                </button>
                            @else
                                <button
                                    type="button"
                                    class="btn btn-sm btn-primary"
                                    wire:click="runCommand(@js([
                                        'cmd' => 'conference',
                                        'name' => $data['head']['conference_name'] ?? '',
                                        'data' => 'lock',
                                    ]))"
                                >
                                    <i class="fa-solid fa-lock"></i> {{__('Lock')}}
                                </button>
                            @endif
                        @endcan

                        @can('conference_interactive_mute')
                            @if(!empty($mute_all) && $mute_all == 'true')
                                <button
                                    type="button"
                                    class="btn btn-sm btn-primary"
                                    wire:click="runCommand(@js([
                                        'cmd' => 'conference',
                                        'name' => $data['head']['conference_name'] ?? '',
                                        'data' => 'unmute+non_moderator',
                                    ]))"
                                >
                                    <i class="fa-solid fa-microphone"></i> {{__('Unmute all')}}
                                </button>
                            @else
                                <button
                                    type="button"
                                    class="btn btn-sm btn-primary"
                                    wire:click="runCommand(@js([
                                        'cmd' => 'conference',
                                        'name' => $data['head']['conference_name'] ?? '',
                                        'data' => 'mute+non_moderator',
                                    ]))"
                                >
                                    <i class="fa-solid fa-microphone-slash"></i> {{__('Mute all')}}
                                </button>
                            @endif
                        @endcan

                        <button
                            type="button"
                            class="btn btn-sm btn-primary"
                            wire:click="runCommand(@js([
                                'cmd' => 'conference',
                                'name' => $data['head']['conference_name'] ?? '',
                                'data' => 'kick+all',
                            ]))"
                        >
                            <i class="fa-solid fa-stop"></i> {{__('End conference')}}
                        </button>

                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="card mb-4">

                    <div class="card-body p-0">
                        <table class="table table-bordered table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>CID Name</th>
                                    <th>CID Number</th>
                                    <th>Joined</th>
                                    <th>Quiet</th>
                                    <th>Has floor</th>
                                    <th>Hand raised</th>
                                    <th>Capabilities</th>

                                    @can('conference_interactive_energy')
                                        <th>Energy</th>
                                    @endcan

                                    @can('conference_interactive_volume')
                                        <th>Volume</th>
                                    @endcan

                                    @can('conference_interactive_gain')
                                        <th>Gain</th>
                                    @endcan

                                    <th>Actions</th>
                                </tr>
                            </thead>

                            <livewire:conference-center-interactive-table :conferenceRoom="$conferenceRoom" :data="$data" />
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
