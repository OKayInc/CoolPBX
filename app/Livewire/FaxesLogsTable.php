<?php

namespace App\Livewire;

use App\Models\Fax;
use App\Models\FaxLog;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class FaxesLogsTable extends DataTableComponent
{
    protected $model = Fax::class;

    public $fax = null;

    public function mount(Fax $fax)
    {
        $this->fax = $fax;
    }

    public function configure(): void
    {
        $canView = auth()->user()->hasPermission('fax_log_view');
        $this->setPrimaryKey('fax_log_uuid')
            ->setTableAttributes([
                'class' => 'table table-striped table-hover table-bordered'
            ])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search Faxes Logs')
            ->setPerPageAccepted([10, 25, 50, 100, 250])
            ->setDefaultPerPage(100)
            ->setTableRowUrl(function ($row) use ($canView)
            {
                return $canView
                    ? route('faxes.logs.view', $row->fax_log_uuid)
                    : null;
            })
            ->setPaginationEnabled();
    }

    public function columns(): array
    {
        $columns = [];

        $columns[] = Column::make("Fax uuid", "fax_log_uuid")->hideIf(true);

        $columns[] = Column::make("Date", "fax_epoch")
                ->format(function ($value, $row, Column $column) {
                    return date("D d M Y H:i:s", $value);
                })
                ->sortable();

        $columns[] = Column::make("Success", "fax_success")
            ->searchable()
            ->sortable();

        $columns[] = Column::make("Code", 'fax_result_code')
            ->searchable()
            ->sortable();

        $columns[] = Column::make("Result", 'fax_result_text')
            ->searchable()
            ->sortable();

        $columns[] = Column::make("File", 'fax_file')
            ->searchable()
            ->sortable();

        $columns[] = Column::make("ECM", 'fax_ecm_used')
            ->searchable()
            ->sortable();

        $columns[] = Column::make("Local station ID", 'fax_local_station_id')
            ->searchable()
            ->sortable();

        $columns[] = Column::make("Bad rows", 'fax_bad_rows')
            ->searchable()
            ->sortable();

        $columns[] = Column::make("Transfer rate", 'fax_transfer_rate')
            ->searchable()
            ->sortable();

        $columns[] = Column::make("Retry", 'fax_retry_attempts')
            ->searchable()
            ->sortable();

        $columns[] = Column::make("Destination", 'fax_uri')
            ->searchable()
            ->sortable();

        return $columns;
    }

    public function builder(): Builder
    {
        $query = FaxLog::query()->where("fax_uuid", $this->fax->fax_uuid);

        return $query;
    }
}
