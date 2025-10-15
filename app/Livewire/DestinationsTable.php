<?php

namespace App\Livewire;

use App\Models\Destination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;

class DestinationsTable extends DataTableComponent
{
    protected $model = Destination::class;

    public $show;
    public $type;

    public function mount($show = null, $type = "inbound")
    {
        $this->show = $show;
        $this->type = $type;
    }

    public function configure(): void
    {
        $canEdit = auth()->user()->hasPermission('destination_edit');
        $this->setPrimaryKey('destination_uuid')
            ->setTableAttributes([
                'class' => 'table table-striped table-hover table-bordered'
            ])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search Destinations')
            ->setPerPageAccepted([10, 25, 50, 100, 250])
            ->setDefaultPerPage(100)
            ->setTableRowUrl(function ($row) use ($canEdit)
            {
                return $canEdit
                    ? route('destinations.edit', $row->destination_uuid)
                    : null;
            })
            ->setPaginationEnabled();
    }

    public function bulkActions(): array
    {
        $bulkActions = [];

        if (auth()->user()->hasPermission('destination_edit'))
        {
            $bulkActions['markEnabled'] = 'Mark as Enabled';
            $bulkActions['markDisabled'] = 'Mark as Disabled';
        }

        if (auth()->user()->hasPermission('destination_delete'))
        {
            $bulkActions['bulkDelete'] = 'Delete';
        }

        if (auth()->user()->hasPermission('destination_add'))
        {
            $bulkActions['bulkCopy'] = 'Copy';
        }

        return $bulkActions;
    }

    public function markEnabled()
    {
        if (!auth()->user()->hasPermission('destination_edit'))
        {
            session()->flash('error', 'You do not have permission to mark destinations as enabled.');
            return;
        }

        $selectedRows = $this->getSelected();

        Destination::whereIn('destination_uuid', $selectedRows)->update(['destination_enabled' => 'true']);

        $this->clearSelected();
        $this->dispatch('refresh');
        session()->flash('success', 'The destinations were successfully enabled.');
    }

    public function markDisabled()
    {
        if (!auth()->user()->hasPermission('destination_edit'))
        {
            session()->flash('error', 'You do not have permission to mark destinations as disabled.');
            return;
        }

        $selectedRows = $this->getSelected();

        Destination::whereIn('destination_uuid', $selectedRows)->update(['destination_enabled' => 'false']);

        $this->clearSelected();
        $this->dispatch('refresh');
        session()->flash('success', 'The destinations were successfully disabled.');
    }


    public function bulkDelete()
    {
        if (!auth()->user()->hasPermission('destination_delete'))
        {
            session()->flash('error', 'You do not have permission to delete destinations.');
            return;
        }

        $selectedRows = $this->getSelected();

        try
        {
            DB::beginTransaction();

            Destination::whereIn('destination_uuid', $selectedRows)->delete();

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');
            session()->flash('success', 'Destinations successfully deleted.');
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            session()->flash('error', 'There was a problem deleting the destinations: ' . $e->getMessage());
        }
    }

    public function bulkCopy()
    {
        if (!auth()->user()->hasPermission('destination_add'))
        {
            session()->flash('error', 'You do not have permission to copy destinations.');
            return;
        }

        $selectedRows = $this->getSelected();

        try
        {
            DB::beginTransaction();

            foreach ($selectedRows as $destinationUuid)
            {
                $originalDestination = Destination::findOrFail($destinationUuid);

                $newDestination = $originalDestination->replicate();
                $newDestination->destination_uuid = Str::uuid();
                $newDestination->destination_description = $newDestination->destination_description . ' (Copy)';
                $newDestination->save();
            }

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            throw $e;
            session()->flash('error', 'There was a problem copying the destinations: ' . $e->getMessage());
        }
    }

    public function columns(): array
    {
        $columns = [];

        $columns[] = Column::make("Destination uuid", "destination_uuid")->hideIf(true);

        $columns[] = Column::make("Type", "destination_type")
            ->sortable();

        $columns[] = Column::make("Prefix", "destination_prefix")
            ->sortable();

        if(auth()->user()->hasPermission('destination_trunk_prefix'))
		{
            $columns[] = Column::make("Context", "destination_trunk_prefix")
                ->sortable();
		}

        if(auth()->user()->hasPermission('destination_area_code'))
		{
            $columns[] = Column::make("Context", "destination_area_code")
                ->sortable();
		}

        $columns[] = Column::make("Number", "destination_number")
            ->sortable();

        if(auth()->user()->hasPermission('destination_context'))
		{
            $columns[] = Column::make("Context", "destination_context")
                ->sortable();
		}

        if(auth()->user()->hasPermission('outbound_caller_id_select'))
		{
            $columns[] = Column::make("Caller ID Name", "destination_caller_id_name")
                ->sortable();

            $columns[] = Column::make("Caller ID Number", "destination_caller_id_number")
                ->sortable();
        }

        $columns[] = BooleanColumn::make("Destination enabled", "destination_enabled")
                ->sortable();

        $columns[] = Column::make("Description", "destination_description")
                ->sortable();

		return $columns;
    }

    public function builder(): Builder
    {
        $query = Destination::with('domain')
            ->orderBy('destination_number', 'asc');

        if($this->show == "all" && auth()->user()->hasPermission('destination_all'))
        {
            $query->where("destination_type", $this->type);
        }
        else
        {
            $query->where("destination_type", $this->type);
            $query->where(function ($q) {
                $q->where(Destination::getTableName() . '.domain_uuid', Session::get('domain_uuid'))
                    ->orWhereNull('domain_uuid');
            });
        }

        return $query;
    }
}
