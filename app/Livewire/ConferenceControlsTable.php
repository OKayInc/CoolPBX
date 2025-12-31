<?php

namespace App\Livewire;

use App\Models\ConferenceControl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;

class ConferenceControlsTable extends DataTableComponent
{
    protected $model = ConferenceControl::class;

    public function configure(): void
    {
        $canEdit = auth()->user()->hasPermission('conference_control_edit');
        $this->setPrimaryKey('conference_control_uuid')
            ->setTableAttributes([
                'class' => 'table table-striped table-hover table-bordered'
            ])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search Conference Controls')
            ->setPerPageAccepted([10, 25, 50, 100, 250])
            ->setDefaultPerPage(100)
            ->setTableRowUrl(function ($row) use ($canEdit)
            {
                return $canEdit
                    ? route('conference_controls.edit', $row->conference_control_uuid)
                    : null;
            })
            ->setPaginationEnabled();
    }

    public function bulkActions(): array
    {
        $bulkActions = [];

        if (auth()->user()->hasPermission('conference_control_edit'))
        {
            $bulkActions['markEnabled'] = 'Mark as Enabled';
            $bulkActions['markDisabled'] = 'Mark as Disabled';
        }

        if (auth()->user()->hasPermission('conference_control_delete'))
        {
            $bulkActions['bulkDelete'] = 'Delete';
        }

        if (auth()->user()->hasPermission('conference_control_add'))
        {
            $bulkActions['bulkCopy'] = 'Copy';
        }

        return $bulkActions;
    }

    public function markEnabled()
    {
        if (!auth()->user()->hasPermission('conference_control_edit'))
        {
            session()->flash('error', 'You do not have permission to mark controls as enabled.');
            return;
        }

        $selectedRows = $this->getSelected();

        ConferenceControl::whereIn('conference_control_uuid', $selectedRows)->update(['control_enabled' => 'true']);

        $this->clearSelected();
        $this->dispatch('refresh');
        session()->flash('success', 'The conference controls were successfully enabled.');
    }

    public function markDisabled()
    {
        if (!auth()->user()->hasPermission('conference_control_edit'))
        {
            session()->flash('error', 'You do not have permission to mark controls as disabled.');
            return;
        }

        $selectedRows = $this->getSelected();

        ConferenceControl::whereIn('conference_control_uuid', $selectedRows)->update(['control_enabled' => 'false']);

        $this->clearSelected();
        $this->dispatch('refresh');
        session()->flash('success', 'The conference controls were successfully disabled.');
    }


    public function bulkDelete()
    {
        if (!auth()->user()->hasPermission('conference_control_delete'))
        {
            session()->flash('error', 'You do not have permission to delete controls.');
            return;
        }

        $selectedRows = $this->getSelected();

        try
        {
            DB::beginTransaction();

            ConferenceControl::whereIn('conference_control_uuid', $selectedRows)->delete();

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');
            session()->flash('success', 'conference controls successfully deleted.');
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            session()->flash('error', 'There was a problem deleting the conference controls: ' . $e->getMessage());
        }
    }

    public function bulkCopy()
    {
        if (!auth()->user()->hasPermission('conference_control_add'))
        {
            session()->flash('error', 'You do not have permission to copy conference controls.');
            return;
        }

        $selectedRows = $this->getSelected();

        try
        {
            DB::beginTransaction();

            foreach ($selectedRows as $bridgeUuid)
            {
                $originalConferenceControl = ConferenceControl::findOrFail($bridgeUuid);

                $newConferenceControl = $originalConferenceControl->replicate();
                $newConferenceControl->conference_control_uuid = Str::uuid();
                $newConferenceControl->control_name = $originalConferenceControl->control_name . ' (Copy)';
                $newConferenceControl->save();
            }

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            throw $e;
            session()->flash('error', 'There was a problem copying the conference controls: ' . $e->getMessage());
        }
    }

    public function columns(): array
    {
        $columns = [
            Column::make("uuid", "conference_control_uuid")->hideIf(true),

            Column::make("Name", "control_name")
                ->sortable(),

            BooleanColumn::make("Enabled", "control_enabled")
                ->sortable(),

            Column::make("Description", "control_description")
            ->sortable()
        ];

        return $columns;
    }

    public function builder(): Builder
    {
        $query = ConferenceControl::query()
            ->orderBy('control_name', 'asc');
        return $query;
    }
}
