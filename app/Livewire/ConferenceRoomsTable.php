<?php

namespace App\Livewire;

use App\Models\ConferenceRoom;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;

class ConferenceRoomsTable extends DataTableComponent
{
    protected $model = ConferenceRoom::class;

    public function configure(): void
    {
        $canEdit = auth()->user()->hasPermission('conference_room_edit');
        $this->setPrimaryKey('conference_room_uuid')
            ->setTableAttributes([
                'class' => 'table table-striped table-hover table-bordered'
            ])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search ConferenceRooms')
            ->setPerPageAccepted([10, 25, 50, 100, 250])
            ->setDefaultPerPage(100)
            ->setTableRowUrl(function ($row) use ($canEdit)
            {
                return $canEdit
                    ? route('conference_rooms.edit', $row->conference_room_uuid)
                    : null;
            })
            ->setPaginationEnabled();
    }

    public function bulkActions(): array
    {
        $bulkActions = [];

        if (auth()->user()->hasPermission('conference_room_edit'))
        {
            $bulkActions['markEnabled'] = 'Mark as Enabled';
            $bulkActions['markDisabled'] = 'Mark as Disabled';
        }

        if (auth()->user()->hasPermission('conference_room_delete'))
        {
            $bulkActions['bulkDelete'] = 'Delete';
        }

        if (auth()->user()->hasPermission('conference_room_add'))
        {
            $bulkActions['bulkCopy'] = 'Copy';
        }

        return $bulkActions;
    }

    public function markEnabled()
    {
        if (!auth()->user()->hasPermission('conference_room_edit'))
        {
            session()->flash('error', 'You do not have permission to mark conference_rooms as enabled.');
            return;
        }

        $selectedRows = $this->getSelected();

        ConferenceRoom::whereIn('conference_room_uuid', $selectedRows)->update(['conference_room_enabled' => 'true']);

        $this->clearSelected();
        $this->dispatch('refresh');
        session()->flash('success', 'The conference rooms were successfully enabled.');
    }

    public function markDisabled()
    {
        if (!auth()->user()->hasPermission('conference_room_edit'))
        {
            session()->flash('error', 'You do not have permission to mark conference_rooms as disabled.');
            return;
        }

        $selectedRows = $this->getSelected();

        ConferenceRoom::whereIn('conference_room_uuid', $selectedRows)->update(['conference_room_enabled' => 'false']);

        $this->clearSelected();
        $this->dispatch('refresh');
        session()->flash('success', 'The conference rooms were successfully disabled.');
    }


    public function bulkDelete()
    {
        if (!auth()->user()->hasPermission('conference_room_delete'))
        {
            session()->flash('error', 'You do not have permission to delete conference_rooms.');
            return;
        }

        $selectedRows = $this->getSelected();

        try
        {
            DB::beginTransaction();

            ConferenceRoom::whereIn('conference_room_uuid', $selectedRows)->delete();

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');
            session()->flash('success', 'conference rooms successfully deleted.');
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            session()->flash('error', 'There was a problem deleting the conference rooms: ' . $e->getMessage());
        }
    }

    public function bulkCopy()
    {
        if (!auth()->user()->hasPermission('conference_room_add'))
        {
            session()->flash('error', 'You do not have permission to copy conference rooms.');
            return;
        }

        $selectedRows = $this->getSelected();

        try
        {
            DB::beginTransaction();

            foreach ($selectedRows as $bridgeUuid)
            {
                $originalConferenceRoom = ConferenceRoom::findOrFail($bridgeUuid);

                $newConferenceRoom = $originalConferenceRoom->replicate();
                $newConferenceRoom->conference_room_uuid = Str::uuid();
                $newConferenceRoom->conference_room_name = $originalConferenceRoom->conference_room_name . ' (Copy)';
                $newConferenceRoom->save();
            }

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            throw $e;
            session()->flash('error', 'There was a problem copying the conference rooms: ' . $e->getMessage());
        }
    }

    public function columns(): array
    {
        $columns = [
            Column::make("uuid", "conference_room_uuid")->hideIf(true),

            Column::make("Name", "conference_room_name")
                ->sortable(),

            Column::make("Moderator", "moderator_pin")
                ->format(function ($value) {
                    if(strlen($value) == 9)
                    {
                        return substr($value, 0, 3) ."-".  substr($value, 3, 3) ."-". substr($value, -3);
                    }
                    else
                    {
                        return $value;
                    }
                })
                ->sortable(),

            Column::make("Greeting", "participant_pin")
                ->format(function ($value) {
                    if(strlen($value) == 9)
                    {
                        return substr($value, 0, 3) ."-".  substr($value, 3, 3) ."-". substr($value, -3);
                    }
                    else
                    {
                        return $value;
                    }
                })
                ->sortable(),

            BooleanColumn::make("Record", "record")
                ->sortable(),

            BooleanColumn::make("Secure", "wait_mod")
                ->sortable(),

            BooleanColumn::make("Announce Name", "announce_name")
                ->sortable(),

            BooleanColumn::make("Announce Count", "announce_count")
                ->sortable(),

            BooleanColumn::make("Announce Recording", "announce_recording")
                ->sortable(),

            BooleanColumn::make("Mute", "mute")
                ->sortable(),

            BooleanColumn::make("Sounds", "sounds")
                ->sortable(),
        ];

        if(auth()->user()->hasPermission('conference_room_enabled'))
        {
            $columns[] = BooleanColumn::make("Enabled", "enabled")->sortable();
        }

        $columns[] = Column::make("Description", "description")->sortable();

        return $columns;
    }

    public function builder(): Builder
    {
        $query = ConferenceRoom::query()
            ->where('domain_uuid', Session::get('domain_uuid'))
            ->orderBy('conference_room_name', 'asc');
        return $query;
    }
}
