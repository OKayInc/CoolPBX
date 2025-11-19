<?php

namespace App\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Illuminate\Database\Eloquent\Builder;
use App\Models\ViewCallRecording;
use Illuminate\Support\Facades\Session;

class CallRecordingsTable extends DataTableComponent
{
    protected $model = ViewCallRecording::class;

    public function configure(): void
    {
        $tableConfig = $this->setPrimaryKey('call_recording_uuid')
            ->setTableAttributes([
                'class' => 'table table-striped table-hover table-bordered'
            ])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search ViewCallRecordings')
            ->setPerPageAccepted([10, 25, 50, 100])
            ->setPaginationEnabled();
    }

    public function columns(): array
    {
        $columns = [
            Column::make("ID", "call_recording_uuid")
                ->hideIf(true),
            Column::make("Caller name", "caller_id_name")
                ->sortable()
                ->searchable(),
            Column::make("Caller number", "caller_id_number")
                ->sortable()
                ->searchable(),
            Column::make("Caller destination", "caller_destination")
                ->sortable()
                ->searchable(),
            Column::make("Destination", "destination_number")
                ->sortable()
                ->searchable(),
            Column::make("Name", "call_recording_name")
                ->sortable()
                ->searchable(),
        ];

        if(auth()->user()->hasPermission('call_recording_play') || auth()->user()->hasPermission('call_recording_download'))
        {
            $columns[] = Column::make("Recording", "caller_id_name")
                ->format(function ($value, $row, Column $column) {
                    return view('components.buttons-audio', [
                        'urlPlay' => route("callrecordings.play", $row->call_recording_uuid),
                        'urlDownload' => route("callrecordings.download", $row->call_recording_uuid),
                    ])->render();
                })
                ->html();
        }

        $columns[] = Column::make("Length", "call_recording_length")
                ->format(function ($value, $row, Column $column) {
                    $seconds = $row->call_recording_length;
                    $minutes = floor(($seconds % 3600) / 60);
                    $secs = $seconds % 60;

                    return sprintf('%02d:%02d', $minutes, $secs);
                })
                ->sortable();

        $columns[] = Column::make("Date", "call_recording_date")
                ->sortable()
                ->format(function ($value, $row, Column $column) {
                    return date('D j M Y H:i:s', strtotime($row->call_recording_date));
                });

        $columns[] = Column::make("Direction", "call_direction")
                ->sortable()
                ->searchable();

        return $columns;
    }


    public function builder(): Builder
    {
        $query = ViewCallRecording::query();

        $showAll = request()->query('show') === 'all';

        if (!$showAll && !auth()->user()->hasPermission('call_recording_all')) {
            $query->where(function ($q) {
                $q->where('domain_uuid', Session::get("domain_uuid"))
                    ->orWhereNull('domain_uuid');
            });
        }

        return $query;
    }
}
