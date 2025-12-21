<?php

namespace App\Livewire;

use App\Models\ConferenceCenter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;

class ConferenceCentersTable extends DataTableComponent
{
    protected $model = ConferenceCenter::class;

    public function configure(): void
    {
        $canEdit = auth()->user()->hasPermission('conference_center_edit');
        $this->setPrimaryKey('conference_center_uuid')
            ->setTableAttributes([
                'class' => 'table table-striped table-hover table-bordered'
            ])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search ConferenceCenters')
            ->setPerPageAccepted([10, 25, 50, 100, 250])
            ->setDefaultPerPage(100)
            ->setTableRowUrl(function ($row) use ($canEdit)
            {
                return $canEdit
                    ? route('conference_centers.edit', $row->conference_center_uuid)
                    : null;
            })
            ->setPaginationEnabled();
    }

    public function bulkActions(): array
    {
        $bulkActions = [];

        if (auth()->user()->hasPermission('conference_center_edit'))
        {
            $bulkActions['markEnabled'] = 'Mark as Enabled';
            $bulkActions['markDisabled'] = 'Mark as Disabled';
        }

        if (auth()->user()->hasPermission('conference_center_delete'))
        {
            $bulkActions['bulkDelete'] = 'Delete';
        }

        if (auth()->user()->hasPermission('conference_center_add'))
        {
            $bulkActions['bulkCopy'] = 'Copy';
        }

        return $bulkActions;
    }

    public function markEnabled()
    {
        if (!auth()->user()->hasPermission('conference_center_edit'))
        {
            session()->flash('error', 'You do not have permission to mark conference_centers as enabled.');
            return;
        }

        $selectedRows = $this->getSelected();

        ConferenceCenter::whereIn('conference_center_uuid', $selectedRows)->update(['conference_center_enabled' => 'true']);

        $this->clearSelected();
        $this->dispatch('refresh');
        session()->flash('success', 'The conference centers were successfully enabled.');
    }

    public function markDisabled()
    {
        if (!auth()->user()->hasPermission('conference_center_edit'))
        {
            session()->flash('error', 'You do not have permission to mark conference_centers as disabled.');
            return;
        }

        $selectedRows = $this->getSelected();

        ConferenceCenter::whereIn('conference_center_uuid', $selectedRows)->update(['conference_center_enabled' => 'false']);

        $this->clearSelected();
        $this->dispatch('refresh');
        session()->flash('success', 'The conference centers were successfully disabled.');
    }


    public function bulkDelete()
    {
        if (!auth()->user()->hasPermission('conference_center_delete'))
        {
            session()->flash('error', 'You do not have permission to delete conference_centers.');
            return;
        }

        $selectedRows = $this->getSelected();

        try
        {
            DB::beginTransaction();

            ConferenceCenter::whereIn('conference_center_uuid', $selectedRows)->delete();

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');
            session()->flash('success', 'conference centers successfully deleted.');
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            session()->flash('error', 'There was a problem deleting the conference centers: ' . $e->getMessage());
        }
    }

    public function bulkCopy()
    {
        if (!auth()->user()->hasPermission('conference_center_add'))
        {
            session()->flash('error', 'You do not have permission to copy conference centers.');
            return;
        }

        $selectedRows = $this->getSelected();

        try
        {
            DB::beginTransaction();

            foreach ($selectedRows as $bridgeUuid)
            {
                $originalConferenceCenter = ConferenceCenter::findOrFail($bridgeUuid);

                $newConferenceCenter = $originalConferenceCenter->replicate();
                $newConferenceCenter->conference_center_uuid = Str::uuid();
                $newConferenceCenter->conference_center_name = $originalConferenceCenter->conference_center_name . ' (Copy)';
                $newConferenceCenter->save();
            }

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            throw $e;
            session()->flash('error', 'There was a problem copying the conference centers: ' . $e->getMessage());
        }
    }

    public function columns(): array
    {
        return [
            Column::make("uuid", "conference_center_uuid")->hideIf(true),

            Column::make("Name", "conference_center_name")
                ->sortable(),

            Column::make("Extension", "conference_center_extension")
                ->sortable(),

            Column::make("Greeting", "conference_center_greeting")
                ->sortable(),

            Column::make("PIN length", "conference_center_pin_length")
                ->sortable(),

            BooleanColumn::make("Enabled", "conference_center_enabled")
                ->sortable(),

            Column::make("Description", "conference_center_description")
                ->sortable(),
        ];
    }

    public function builder(): Builder
    {
        $query = ConferenceCenter::query()
            ->where('domain_uuid', Session::get('domain_uuid'))
            ->orderBy('conference_center_name', 'asc');
        return $query;
    }
}
