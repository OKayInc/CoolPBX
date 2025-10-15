<?php

namespace App\Livewire;

use App\Models\Fax;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;

class FaxesTable extends DataTableComponent
{
    protected $model = Fax::class;

    public function configure(): void
    {
        $canEdit = auth()->user()->hasPermission('fax_edit');
        $this->setPrimaryKey('fax_uuid')
            ->setTableAttributes([
                'class' => 'table table-striped table-hover table-bordered'
            ])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search Faxes')
            ->setPerPageAccepted([10, 25, 50, 100, 250])
            ->setDefaultPerPage(100)
            ->setTableRowUrl(function ($row) use ($canEdit)
            {
                return $canEdit
                    ? route('faxes.edit', $row->fax_uuid)
                    : null;
            })
            ->setPaginationEnabled();
    }

    public function bulkActions(): array
    {
        $bulkActions = [];

        if (auth()->user()->hasPermission('fax_delete'))
        {
            $bulkActions['bulkDelete'] = 'Delete';
        }

        if (auth()->user()->hasPermission('fax_add'))
        {
            $bulkActions['bulkCopy'] = 'Copy';
        }

        return $bulkActions;
    }

    public function bulkDelete()
    {
        if (!auth()->user()->hasPermission('fax_delete'))
        {
            session()->flash('error', 'You do not have permission to delete faxes.');
            return;
        }

        $selectedRows = $this->getSelected();

        try
        {
            DB::beginTransaction();

            Fax::whereIn('fax_uuid', $selectedRows)->delete();

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');
            session()->flash('success', 'Faxes successfully deleted.');
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            session()->flash('error', 'There was a problem deleting the faxes: ' . $e->getMessage());
        }
    }

    public function bulkCopy()
    {
        if (!auth()->user()->hasPermission('fax_add'))
        {
            session()->flash('error', 'You do not have permission to copy faxes.');
            return;
        }

        $selectedRows = $this->getSelected();

        try
        {
            DB::beginTransaction();

            foreach ($selectedRows as $faxUuid)
            {
                $originalFax = Fax::findOrFail($faxUuid);

                $newFax = $originalFax->replicate();
                $newFax->fax_uuid = Str::uuid();
                $newFax->fax_name = $newFax->fax_name . ' (Copy)';
            }

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            throw $e;
            session()->flash('error', 'There was a problem copying the faxes: ' . $e->getMessage());
        }
    }

    public function columns(): array
    {
        return [
            Column::make("Fax uuid", "fax_uuid")->hideIf(true),

            Column::make("Name", "fax_name")
                ->searchable()
                ->sortable(),

            Column::make("Extension", "fax_extension")
                ->searchable()
                ->sortable(),

            Column::make("Email", "fax_email")
                ->searchable()
                ->sortable(),

            Column::make("Description", "fax_description")
                ->searchable()
                ->sortable(),

            Column::make("Tools", "fax_uuid")
                ->format(function ($value, $row, Column $column) {
                    $buttons = "";
                    $tools = "";

                    if(auth()->user()->hasPermission('fax_send'))
                    {
                        $buttons .= '<a href="#" class="btn btn-primary btn-sm m-1"><i class="fa-solid fa-paper-plane"></i></a>';
                    }

                    if(auth()->user()->hasPermission('fax_inbox_view'))
                    {
                        $buttons .= '<a href="#" class="btn btn-primary btn-sm m-1"><i class="fa-solid fa-inbox"></i></a>';
                    }

                    if(auth()->user()->hasPermission('fax_sent_view'))
                    {
                        $buttons .= '<a href="#" class="btn btn-primary btn-sm m-1"><i class="fa-solid fa-envelope-circle-check"></i></a>';
                    }

                    if(auth()->user()->hasPermission('fax_log_view'))
                    {
                        $buttons .= '<a href="#" class="btn btn-primary btn-sm m-1"><i class="fa-solid fa-file-lines"></i></a>';
                    }

                    if(auth()->user()->hasPermission('fax_active_view'))
                    {
                        $buttons .= '<a href="#" class="btn btn-primary btn-sm m-1"><i class="fa-solid fa-square-check"></i></a>';
                    }

                    if(auth()->user()->hasPermission('fax_queue_view'))
                    {
                        $buttons .= '<a href="#" class="btn btn-primary btn-sm m-1"><i class="fa-solid fa-business-time"></i></a>';
                    }

                    return $buttons;
                })
                ->html(),
        ];
    }

    public function builder(): Builder
    {
        $query = Fax::query()
            ->orderBy('fax_name', 'asc');
        return $query;
    }
}
