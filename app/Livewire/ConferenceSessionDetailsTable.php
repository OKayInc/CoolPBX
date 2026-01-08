<?php

namespace App\Livewire;

use App\Models\ConferenceSession;
use App\Models\ConferenceSessionDetail;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class ConferenceSessionDetailsTable extends DataTableComponent
{
    protected $model = ConferenceSessionDetail::class;

    public ConferenceSession $conferenceSession;

    public function mount(ConferenceSession $conferenceSession)
    {
        $this->conferenceSession = $conferenceSession;
    }

    public function configure(): void
    {
        $canViewDetails = auth()->user()->hasPermission('conference_session_details');
        $this->setPrimaryKey('conference_session_uuid')
            ->setTableAttributes([
                'class' => 'table table-striped table-hover table-bordered'
            ])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search Conference Sessions')
            ->setPerPageAccepted([10, 25, 50, 100, 250])
            ->setDefaultPerPage(100)
            ->setTableRowUrl(function ($row) use ($canViewDetails)
            {
                return $canViewDetails
                    ? route('xmlcdr.details', $row->uuid)
                    : null;
            })
            ->setPaginationEnabled();
    }

    public function columns(): array
    {
        $columns = [
            Column::make("uuid", "uuid")->hideIf(true),

            Column::make("Caller ID Name", "caller_id_name")
                ->sortable(),

            Column::make("Caller ID Number", "caller_id_number")
                ->sortable(),

            Column::make("Moderator", "moderator")
                ->sortable(),

            Column::make("Network address", "network_addr")
                ->sortable(),

            Column::make("Time", "end_epoch")
                ->format(function ($value, $row, Column $column) {
					$time_difference = $row->end_epoch - $row->start_epoch;

					return gmdate("G:i:s", $time_difference);
                })
                ->sortable(),

            Column::make("Start", "start_epoch")
                ->format(function ($value, $row, Column $column) {
                    return date("j M Y H:i:s", $value);
                })
                ->sortable(),

            Column::make("End", "end_epoch")
                ->format(function ($value, $row, Column $column) {
                    return date("j M Y H:i:s", $value);
                })
                ->sortable(),
		];

        return $columns;
    }

    public function builder(): Builder
    {
        $query = ConferenceSessionDetail::query()
            ->where('conference_session_uuid', $this->conferenceSession->conference_session_uuid);
        return $query;
    }
}
