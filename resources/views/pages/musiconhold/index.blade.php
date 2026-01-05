@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="mt-3 card card-primary card-outline">
            
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="fas fa-music mr-2"></i> {{ __('Music On Hold') }}
                </h3>

                <div class="card-tools">
                    <div class="d-flex gap-2" role="group" aria-label="Group actions">
                        @can('music_on_hold_add')
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                <i class="fas fa-plus mr-1"></i> {{ __('Upload Music') }}
                            </button>
                        @endcan
                    </div>
                </div>
            </div>

            <div class="card-body">
                
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @forelse($groupedList as $categoryName => $rates)
                    
                    <div class="border rounded mb-4 overflow-hidden">
                        <div class="bg-light p-3 border-bottom d-flex align-items-center">
                            <i class="fas fa-layer-group text-primary mr-2"></i>
                            <h5 class="mb-0 fw-bold text-dark">{{ $categoryName }}</h5>
                        </div>

                        @foreach($rates as $item)
                            <div class="rate-group border-bottom last-no-border">
                                <div class="px-3 py-2 bg-white d-flex align-items-center justify-content-between border-bottom border-light">
                                    <span class="badge badge-info">
                                        <i class="fas fa-wave-square mr-1"></i> {{ $item['rate'] }} Hz
                                    </span>
                                    <small class="text-muted font-monospace">{{ $item['path'] }}</small>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover table-striped align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px;" class="text-center"></th>
                                                <th>{{ __('Filename') }}</th>
                                                <th style="width: 15%;">{{ __('Size') }}</th>
                                                <th style="width: 20%;">{{ __('Uploaded') }}</th>
                                                <th style="width: 15%;" class="text-right">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($item['files'] as $file)
                                                <tr>
                                                    <td class="text-center">
                                                        <button class="btn btn-xs btn-outline-primary rounded-circle btn-play-audio" 
                                                                data-src="{{ route('musiconhold.play', [$item['uuid'], $file['name']]) }}"
                                                                data-uid="{{ $file['uid'] }}">
                                                            <i class="fas fa-play"></i>
                                                        </button>
                                                    </td>
                                                    <td>
                                                        <span class="fw-bold text-dark">{{ $file['name'] }}</span>
                                                        <audio id="audio-{{ $file['uid'] }}" preload="none">
                                                            <source src="{{ route('musiconhold.play', [$item['uuid'], $file['name']]) }}" type="audio/mpeg">
                                                        </audio>
                                                    </td>
                                                    <td class="text-muted small">{{ $file['size'] }}</td>
                                                    <td class="text-muted small">{{ $file['uploaded'] }}</td>
                                                    <td class="text-right">
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="{{ route('musiconhold.download', [$item['uuid'], $file['name']]) }}" 
                                                               class="btn btn-default" 
                                                               title="{{ __('Download') }}">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                            
                                                            @can('music_on_hold_delete')
                                                                <form action="{{ route('musiconhold.destroyFile', [$item['uuid'], $file['name']]) }}" 
                                                                      method="POST" 
                                                                      class="d-inline-block"
                                                                      onsubmit="return confirm('{{ __('Are you sure you want to delete this file?') }}');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-default text-danger" title="{{ __('Delete') }}">
                                                                        <i class="fas fa-trash-alt"></i>
                                                                    </button>
                                                                </form>
                                                            @endcan
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-3 text-muted">
                                                        <small>{{ __('No audio files in this directory') }}</small>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @empty
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-music fa-3x text-muted opacity-25"></i>
                        </div>
                        <h5 class="text-muted">{{ __('No Music on Hold found') }}</h5>
                    </div>
                @endforelse
            </div>
            </div>
        </div>

    @include('pages.musiconhold.modal') 

@endsection

@push('styles')
<style>
    .last-no-border:last-child { border-bottom: none !important; }
    .btn-play-audio { width: 28px; height: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }
    .badge-info { background-color: #17a2b8; color: white; }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const playButtons = document.querySelectorAll('.btn-play-audio');
        let currentAudio = null;
        let currentBtn = null;

        playButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const uid = this.dataset.uid;
                const audio = document.getElementById('audio-' + uid);
                const icon = this.querySelector('i');

                if (currentAudio === audio && !audio.paused) {
                    audio.pause();
                    icon.classList.remove('fa-pause');
                    icon.classList.add('fa-play');
                    this.classList.remove('btn-primary', 'text-white');
                    this.classList.add('btn-outline-primary');
                    return;
                }

                // Stop currently playing
                if (currentAudio) {
                    currentAudio.pause();
                    currentAudio.currentTime = 0;
                    if(currentBtn) {
                        const prevIcon = currentBtn.querySelector('i');
                        prevIcon.classList.remove('fa-pause');
                        prevIcon.classList.add('fa-play');
                        currentBtn.classList.remove('btn-primary', 'text-white');
                        currentBtn.classList.add('btn-outline-primary');
                    }
                }

                currentAudio = audio;
                currentBtn = this;
                
                audio.play().then(() => {
                    icon.classList.remove('fa-play');
                    icon.classList.add('fa-pause');
                    this.classList.remove('btn-outline-primary');
                    this.classList.add('btn-primary', 'text-white');
                }).catch(e => console.error("Audio play error", e));

                // Reset when ended
                audio.onended = () => {
                    icon.classList.remove('fa-pause');
                    icon.classList.add('fa-play');
                    this.classList.remove('btn-primary', 'text-white');
                    this.classList.add('btn-outline-primary');
                    currentAudio = null;
                };
            });
        });
    });
</script>
@endpush