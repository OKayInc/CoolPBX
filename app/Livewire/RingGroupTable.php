<?php

namespace App\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\RingGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RingGroupTable extends DataTableComponent
{
    protected $model = RingGroup::class;
    public bool $show_all = false;

    public function configure(): void
    {
        $canEdit = auth()->user()->hasPermission('ring_group_edit');
        $tableConfig = $this->setPrimaryKey('ring_group_uuid')
            ->setTableAttributes([
                'class' => 'table table-striped table-hover table-bordered'
            ])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search Ring Groups')
            ->setPerPageAccepted([10, 25, 50, 100])
            ->setPaginationEnabled();

        if ($canEdit) {
            $tableConfig->setTableRowUrl(function ($row) use ($canEdit) {
                return route('ring_groups.edit', $row->ring_group_uuid);
            });
        }
    }

    public function bulkActions(): array
    {
        $bulkActions = [];

        if (auth()->user()->hasPermission('ring_group_edit')) {
            $bulkActions['toogleRingGroup'] = 'Toggle';
        }

        if (auth()->user()->hasPermission('ring_group_delete')) {
            $bulkActions['bulkDelete'] = 'Delete';
        }

        if (auth()->user()->hasPermission('ring_group_add')) {
            $bulkActions['bulkCopy'] = 'Copy';
        }

        return $bulkActions;
    }

    public function toogleRingGroup(): void
    {
        if (!auth()->user()->hasPermission('ring_group_edit')) {
            session()->flash('error', 'You do not have permission to toggle ring groups.');
            return;
        }

        $selectRows = $this->getSelected();

        RingGroup::whereIn('ring_group_uuid', $selectRows)
            ->update([
                'ring_group_enabled' => DB::raw("CASE WHEN ring_group_enabled = 'true' THEN 'false' ELSE 'true' END")
            ]);

        $this->clearSelected();
        $this->dispatch('refresh');

        session()->flash('success', 'Ring groups toggled successfully.');
    }

    public function bulkCopy(): void
    {
        if (!auth()->user()->hasPermission('ring_group_add')) {
            session()->flash('error', 'You do not have permission to copy ring groups.');
            return;
        }

        $selectRows = $this->getSelected();

        try {
            DB::beginTransaction();

            foreach ($selectRows as $ringGroupUuid) {
                $original = RingGroup::with(['users', 'destinations', 'dialplan'])
                    ->where('ring_group_uuid', $ringGroupUuid)
                    ->first();

                if (!$original) {
                    continue;
                }

                $newRingGroupUuid = Str::uuid()->toString();
                $newDialplanUuid = Str::uuid()->toString();

                $newRingGroup = $original->replicate();
                $newRingGroup->ring_group_uuid = $newRingGroupUuid;
                $newRingGroup->dialplan_uuid = $newDialplanUuid;
                $newRingGroup->ring_group_description = trim($original->ring_group_description . ' (copy)');
                $newRingGroup->save();

                foreach ($original->users as $user) {
                    $newUser = $user->replicate();
                    $newUser->ring_group_user_uuid = Str::uuid()->toString();
                    $newUser->ring_group_uuid = $newRingGroupUuid;
                    $newUser->save();
                }

                foreach ($original->destinations as $dest) {
                    $newDest = $dest->replicate();
                    $newDest->ring_group_destination_uuid = Str::uuid()->toString();
                    $newDest->ring_group_uuid = $newRingGroupUuid;
                    $newDest->save();
                }

                if ($original->dialplan) {
                    $newDialplan = $original->dialplan->replicate();
                    $newDialplan->dialplan_uuid = $newDialplanUuid;
                    $newDialplan->dialplan_description = trim($original->dialplan->dialplan_description . ' (copy)');

                    $dialplanXml = str_replace($original->ring_group_uuid, $newRingGroupUuid, $original->dialplan->dialplan_xml);
                    $dialplanXml = str_replace($original->dialplan_uuid, $newDialplanUuid, $dialplanXml);
                    $newDialplan->dialplan_xml = $dialplanXml;

                    $newDialplan->save();
                }
            }

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');

            session()->flash('success', 'Ring groups copied successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            session()->flash('error', 'An error occurred while copying ring groups.');
        }
    }

    public function bulkDelete(): void
    {
        if (!auth()->user()->hasPermission('ring_group_delete')) {
            session()->flash('error', 'You do not have permission to delete ring groups.');
            return;
        }

        $selectedRows = $this->getSelected();

        try {
            DB::beginTransaction();

            RingGroup::whereIn('ring_group_uuid', $selectedRows)->delete();

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');

            session()->flash('success', 'Ring groups deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            session()->flash('error', 'An error occurred while deleting ring groups.');
        }
    }


    public function columns(): array
    {

        $columns = [
            Column::make("ID", "ring_group_uuid")
                ->sortable()
                ->searchable()
                ->hideIf(true),
            Column::make("Domain", "domain_uuid")
                ->sortable()
                ->searchable()
                ->hideIf(true),
            Column::make("Name", "ring_group_name")
                ->sortable(),
            Column::make("Extension", "ring_group_extension")
                ->sortable(),
            Column::make("Strategy", "ring_group_strategy")
                ->sortable(),
            Column::make("Forwarding", "ring_group_call_forward_enabled")
                ->sortable(),
            Column::make("Enabled", "ring_group_enabled")
                ->sortable(),
            Column::make("Description", "ring_group_description")
                ->sortable()
                ->searchable(),
        ];

        if ($this->show_all) {
            array_splice($columns, 3, 0, [
                Column::make("Domain", "domain_name")
                    ->sortable()
                    ->searchable(),
            ]);
        }

        return $columns;
    }

    public function builder(): Builder
    {
        $query = RingGroup::query();

        if($this->show_all){
            $query->leftJoin('v_domains', 'v_ring_groups.domain_uuid', '=', 'v_domains.domain_uuid')
                  ->select('v_ring_groups.*', 'v_domains.domain_name');
        } else {
            $query->where(function ($q) {
                $q->where('v_ring_groups.domain_uuid', auth()->user()->domain_uuid)
                  ->orWhereNull('v_ring_groups.domain_uuid');
            });
        }

        return $query;
    }
}
