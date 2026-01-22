<?php

namespace App\Livewire;

use App\Models\Conference;
use App\Models\ConferenceUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;

class ConferencesTable extends DataTableComponent
{
    protected $model = Conference::class;

    public function configure(): void
    {
        $canEdit = auth()->user()->hasPermission('conference_edit');
        $this->setPrimaryKey('conference_uuid')
            ->setTableAttributes([
                'class' => 'table table-striped table-hover table-bordered'
            ])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search Conferences')
            ->setPerPageAccepted([10, 25, 50, 100, 250])
            ->setDefaultPerPage(100)
            ->setTableRowUrl(function ($row) use ($canEdit)
            {
                return $canEdit
                    ? route('conferences.edit', $row->conference_uuid)
                    : null;
            })
            ->setPaginationEnabled();
    }

    public function bulkActions(): array
    {
        $bulkActions = [];

        if (auth()->user()->hasPermission('conference_edit'))
        {
            $bulkActions['markEnabled'] = 'Mark as Enabled';
            $bulkActions['markDisabled'] = 'Mark as Disabled';
        }

        if (auth()->user()->hasPermission('conference_delete'))
        {
            $bulkActions['bulkDelete'] = 'Delete';
        }

        if (auth()->user()->hasPermission('conference_add'))
        {
            $bulkActions['bulkCopy'] = 'Copy';
        }

        return $bulkActions;
    }

    public function markEnabled()
    {
        if (!auth()->user()->hasPermission('conference_edit'))
        {
            session()->flash('error', 'You do not have permission to mark conferences as enabled.');
            return;
        }

        $selectedRows = $this->getSelected();

        Conference::whereIn('conference_uuid', $selectedRows)->update(['conference_enabled' => 'true']);

        $this->clearSelected();
        $this->dispatch('refresh');
        session()->flash('success', 'The conference s were successfully enabled.');
    }

    public function markDisabled()
    {
        if (!auth()->user()->hasPermission('conference_edit'))
        {
            session()->flash('error', 'You do not have permission to mark conferences as disabled.');
            return;
        }

        $selectedRows = $this->getSelected();

        Conference::whereIn('conference_uuid', $selectedRows)->update(['conference_enabled' => 'false']);

        $this->clearSelected();
        $this->dispatch('refresh');
        session()->flash('success', 'The conference s were successfully disabled.');
    }


    public function bulkDelete()
    {
        if (!auth()->user()->hasPermission('conference_delete'))
        {
            session()->flash('error', 'You do not have permission to delete conferences.');
            return;
        }

        $selectedRows = $this->getSelected();

        try
        {
            DB::beginTransaction();

            Conference::whereIn('conference_uuid', $selectedRows)->delete();

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');
            session()->flash('success', 'conference s successfully deleted.');
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            session()->flash('error', 'There was a problem deleting the conference s: ' . $e->getMessage());
        }
    }

    public function bulkCopy()
    {
        if (!auth()->user()->hasPermission('conference_add'))
        {
            session()->flash('error', 'You do not have permission to copy conference s.');
            return;
        }

        $selectedRows = $this->getSelected();

        try
        {
            DB::beginTransaction();

            foreach ($selectedRows as $bridgeUuid)
            {
                $originalConference = Conference::findOrFail($bridgeUuid);

                $newConference = $originalConference->replicate();
                $newConference->conference_uuid = Str::uuid();
                $newConference->conference_name = $originalConference->conference_name . ' (Copy)';
                $newConference->save();
            }

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            throw $e;
            session()->flash('error', 'There was a problem copying the conference s: ' . $e->getMessage());
        }
    }

    public function columns(): array
    {
        $columns = [
            Column::make("uuid", "conference_uuid")->hideIf(true),

            Column::make("Name", "conference_name")
                ->sortable(),

			Column::make("Extension", "conference_extension")
			->sortable(),

			Column::make("Profile", "conference_profile")
			->sortable(),

            BooleanColumn::make("Enabled", "conference_enabled")
                ->sortable(),

			Column::make("Description", "conference_description")
			->sortable()
        ];

        return $columns;
    }

    public function builder(): Builder
    {
        if(auth()->user()->hasGroup(['superadmin', 'admin']))
        {
            $query = Conference::query()
                ->where(function ($q)
                {
                    $q->where('domain_uuid', Session::get('domain_uuid'))->orWhereNull('domain_uuid');
                });
        }
        else
        {
            $userUuid = auth()->user()->user_uuid;

            $query = Conference::query()
                ->where('domain_uuid', Session::get('domain_uuid'))
                ->whereHas('users', function ($q) use ($userUuid)
                {
                    $q->where(ConferenceUser::getTableName() . '.user_uuid', $userUuid);
                });
        }

        return $query;
    }
}
