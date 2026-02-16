<?php

namespace App\Livewire;

use App\Facades\Setting;
use App\Models\Fax;
use App\Models\FaxQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;

class FaxesQueuesTable extends DataTableComponent
{
    protected $model = Fax::class;

    public function configure(): void
    {
        $canEdit = auth()->user()->hasPermission('fax_queue_edit');
        $this->setPrimaryKey('fax_queue_uuid')
            ->setTableAttributes([
                'class' => 'table table-striped table-hover table-bordered'
            ])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search Faxes Queues')
            ->setPerPageAccepted([10, 25, 50, 100, 250])
            ->setDefaultPerPage(100)
            ->setTableRowUrl(function ($row) use ($canEdit)
            {
                return $canEdit
                    ? route('fax_queue.edit', $row->fax_queue_uuid)
                    : null;
            })
            ->setPaginationEnabled();
    }

    public function bulkActions(): array
    {
        $bulkActions = [];

        if (auth()->user()->hasPermission('fax_queue_delete'))
        {
            $bulkActions['bulkDelete'] = 'Delete';
        }

        return $bulkActions;
    }

    public function bulkDelete()
    {
        if (!auth()->user()->hasPermission('fax_queue_delete'))
        {
            session()->flash('error', 'You do not have permission to delete fax queues.');
            return;
        }

        $selectedRows = $this->getSelected();

        try
        {
            DB::beginTransaction();

            Fax::whereIn('fax_queue_uuid', $selectedRows)->delete();

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');
            session()->flash('success', 'Fax Queue successfully deleted.');
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            session()->flash('error', 'There was a problem deleting the fax queues: ' . $e->getMessage());
        }
    }

    public function columns(): array
    {
        return [
            Column::make("Fax uuid", "fax_queue_uuid")->hideIf(true),

            Column::make("Date", "fax_date")
                ->format(function ($value, $row, Column $column) {
                    return date("D d M Y H:i:s", strtotime($value));
                })
                ->sortable(),

            Column::make("Hostname", "hostname")
                ->searchable()
                ->sortable(),

            Column::make("Caller ID Name", "fax_caller_id_name")
                ->searchable()
                ->sortable(),

            Column::make("Caller ID Number", "fax_caller_id_number")
                ->searchable()
                ->sortable(),

            Column::make("Number", "fax_number")
                ->searchable()
                ->sortable(),

            Column::make("Email address", "fax_email_address")
                ->searchable()
                ->sortable(),

            Column::make("Status", "fax_status")
                ->searchable()
                ->sortable(),

            Column::make("Retry date", "fax_retry_date")
                ->format(function ($value, $row, Column $column) {
                    return date("D d M Y H:i:s", strtotime($value));
                })
                ->sortable(),

            Column::make("Notify date", "fax_notify_date")
                ->format(function ($value, $row, Column $column) {
                    return date("D d M Y H:i:s", strtotime($value));
                })
                ->sortable(),

            Column::make("Retry count", "fax_retry_count")
                ->searchable()
                ->sortable(),
        ];
    }

    public function builder(): Builder
    {
        $query = FaxQueue::query()
            ->where("domain_uuid", Session::get("domain_uuid"));

        return $query;
    }
}
