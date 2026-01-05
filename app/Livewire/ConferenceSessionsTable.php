<?php

namespace App\Livewire;

use App\Facades\Setting;
use App\Models\ConferenceSession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;

class ConferenceSessionsTable extends DataTableComponent
{
    protected $model = ConferenceSession::class;

    public function configure(): void
    {
        $this->setPrimaryKey('conference_session_uuid')
            ->setTableAttributes([
                'class' => 'table table-striped table-hover table-bordered'
            ])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search Conference Sessions')
            ->setPerPageAccepted([10, 25, 50, 100, 250])
            ->setDefaultPerPage(100)
            ->setTableRowUrl(function ($row)
            {
                return null;
            })
            ->setPaginationEnabled();
    }

    public function columns(): array
    {
        $columns = [
            Column::make("uuid", "conference_session_uuid")->hideIf(true),

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

            Column::make("Time", "end_epoch")
                ->format(function ($value, $row, Column $column) {
					$time_difference = $row->end_epoch - $row->start_epoch;

					return gmdate("G:i:s", $time_difference);
                })
                ->sortable(),

            Column::make("Profile", "profile")
                ->sortable(),

			Column::make("Tools", "conference_session_uuid")
                ->format(function ($value, $row, Column $column) {

					$tools = "";

					if(auth()->user()->hasPermission('conference_session_play'))
					{
						$tools = view('components.buttons-audio', [
							'urlPlay' => route("conference_centers.play", $row->conference_session_uuid),
							'urlDownload' => route("conference_centers.download", $row->conference_session_uuid),
						])->render();
					}

					return $tools;
                })
                ->html()
		];

        return $columns;
    }

    public function builder(): Builder
    {
        $query = ConferenceSession::query()
            ->where('domain_uuid', Session::get('domain_uuid'))
            ->where('meeting_uuid', Setting::getSetting('meeting', 'uuid'));
        return $query;
    }
}
