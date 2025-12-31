<?php

namespace App\Livewire;

use App\Models\ConferenceProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;

class ConferenceProfilesTable extends DataTableComponent
{
    protected $model = ConferenceProfile::class;

    public function configure(): void
    {
        $canEdit = auth()->user()->hasPermission('conference_profile_edit');
        $this->setPrimaryKey('conference_profile_uuid')
            ->setTableAttributes([
                'class' => 'table table-striped table-hover table-bordered'
            ])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search Conference Profiles')
            ->setPerPageAccepted([10, 25, 50, 100, 250])
            ->setDefaultPerPage(100)
            ->setTableRowUrl(function ($row) use ($canEdit)
            {
                return $canEdit
                    ? route('conference_profiles.edit', $row->conference_profile_uuid)
                    : null;
            })
            ->setPaginationEnabled();
    }

    public function bulkActions(): array
    {
        $bulkActions = [];

        if (auth()->user()->hasPermission('conference_profile_edit'))
        {
            $bulkActions['markEnabled'] = 'Mark as Enabled';
            $bulkActions['markDisabled'] = 'Mark as Disabled';
        }

        if (auth()->user()->hasPermission('conference_profile_delete'))
        {
            $bulkActions['bulkDelete'] = 'Delete';
        }

        if (auth()->user()->hasPermission('conference_profile_add'))
        {
            $bulkActions['bulkCopy'] = 'Copy';
        }

        return $bulkActions;
    }

    public function markEnabled()
    {
        if (!auth()->user()->hasPermission('conference_profile_edit'))
        {
            session()->flash('error', 'You do not have permission to mark profiles as enabled.');
            return;
        }

        $selectedRows = $this->getSelected();

        ConferenceProfile::whereIn('conference_profile_uuid', $selectedRows)->update(['profile_enabled' => 'true']);

        $this->clearSelected();
        $this->dispatch('refresh');
        session()->flash('success', 'The conference profiles were successfully enabled.');
    }

    public function markDisabled()
    {
        if (!auth()->user()->hasPermission('conference_profile_edit'))
        {
            session()->flash('error', 'You do not have permission to mark profiles as disabled.');
            return;
        }

        $selectedRows = $this->getSelected();

        ConferenceProfile::whereIn('conference_profile_uuid', $selectedRows)->update(['profile_enabled' => 'false']);

        $this->clearSelected();
        $this->dispatch('refresh');
        session()->flash('success', 'The conference profiles were successfully disabled.');
    }


    public function bulkDelete()
    {
        if (!auth()->user()->hasPermission('conference_profile_delete'))
        {
            session()->flash('error', 'You do not have permission to delete profiles.');
            return;
        }

        $selectedRows = $this->getSelected();

        try
        {
            DB::beginTransaction();

            ConferenceProfile::whereIn('conference_profile_uuid', $selectedRows)->delete();

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');
            session()->flash('success', 'conference profiles successfully deleted.');
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            session()->flash('error', 'There was a problem deleting the conference profiles: ' . $e->getMessage());
        }
    }

    public function bulkCopy()
    {
        if (!auth()->user()->hasPermission('conference_profile_add'))
        {
            session()->flash('error', 'You do not have permission to copy conference profiles.');
            return;
        }

        $selectedRows = $this->getSelected();

        try
        {
            DB::beginTransaction();

            foreach ($selectedRows as $bridgeUuid)
            {
                $originalConferenceProfile = ConferenceProfile::findOrFail($bridgeUuid);

                $newConferenceProfile = $originalConferenceProfile->replicate();
                $newConferenceProfile->conference_profile_uuid = Str::uuid();
                $newConferenceProfile->profile_name = $originalConferenceProfile->profile_name . ' (Copy)';
                $newConferenceProfile->save();
            }

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            throw $e;
            session()->flash('error', 'There was a problem copying the conference profiles: ' . $e->getMessage());
        }
    }

    public function columns(): array
    {
        $columns = [
            Column::make("uuid", "conference_profile_uuid")->hideIf(true),

            Column::make("Name", "profile_name")
                ->sortable(),

            BooleanColumn::make("Enabled", "profile_enabled")
                ->sortable(),

            Column::make("Description", "profile_description")
            ->sortable()
        ];

        return $columns;
    }

    public function builder(): Builder
    {
        $query = ConferenceProfile::query()
            ->orderBy('profile_name', 'asc');
        return $query;
    }
}
