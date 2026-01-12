<?php

namespace App\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\Dialplan;
use App\Models\DialplanDetail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TimeConditionTable extends DataTableComponent
{
    protected $model = Dialplan::class;
    public bool $showAll = false;

    public function configure(): void
    {
        $canEdit = auth()->user()->hasPermission("time_condition_edit");
        $tableConfig = $this->setPrimaryKey('dialplan_uuid')
            ->setTableAttributes([
                'class' => 'table table-striped table-hover table-bordered'
            ])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search time conditions')
            ->setPerPageAccepted([10, 25, 50, 100])
            ->setPaginationEnabled();
        if ($canEdit) {
            $tableConfig->setTableRowUrl(function ($row) use ($canEdit) {
                return route('time_conditions.edit', $row->dialplan_uuid);
            });
        }

        if(request('showAll'))
        {
            $this->showAll = true;
        }
    }

    public function columns(): array
    {
        $columns = [
            Column::make("Dialplan uuid", "dialplan_uuid")
                ->sortable()
                ->hideIf(true),
            Column::make("Name", "dialplan_name")
                ->sortable(),
            Column::make("Number", "dialplan_number")
                ->sortable(),
            Column::make("Context", "dialplan_context")
                ->sortable(),
            Column::make("Order", "dialplan_order")
                ->sortable(),
            Column::make("Enabled", "dialplan_enabled")
                ->sortable(),
            Column::make("Description", "dialplan_description")
                ->sortable(),
        ];

        if($this->showAll){
            $columns[] = Column::make('Domain name', 'domain.domain_name')
                ->searchable()
                ->sortable();
        }
        return $columns;

    }

    public function bulkActions(): array
    {
        $actions = [];

        if (auth()->user()->hasPermission('time_condition_delete')) {
            $actions['bulkDelete'] = 'Delete';
        }

        if (auth()->user()->hasPermission('time_condition_add')) {
            $actions['bulkCopy'] = 'Copy';
        }

        if (auth()->user()->hasPermission('time_condition_edit')) {
            $actions['bulkToggle'] = 'Toggle';
        }
        return $actions;
    }

    public function bulkToggle()
    {
        $selectRows = $this->getSelected();

        Dialplan::whereIn('dialplan_uuid', $selectRows)
            ->update([
                'dialplan_enabled' => DB::raw("CASE WHEN dialplan_enabled = 'true' THEN 'false' ELSE 'true' END")
            ]);

        $this->clearSelected();
        $this->dispatch('refresh');
        
        session()->flash('message', 'Device status toggled successfully');
    }

    public function bulkDelete()
    {
        $selectRows = $this->getSelected();

        try {
            DB::beginTransaction();

            Dialplan::whereIn('dialplan_uuid', $selectRows)->delete();
            DialplanDetail::whereIn('dialplan_uuid', $selectRows)->delete();

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');

            session()->flash('message', 'Time Conditions deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred while deleting Time Conditions: ' . $e->getMessage());
        }
    }

    public function bulkCopy()
    {
        $selectRows = $this->getSelected();

        try {
            DB::beginTransaction();

            foreach ($selectRows as $dialplanUuid) {
                $originalDialplan = Dialplan::with('dialplanDetails')->find($dialplanUuid);

                if ($originalDialplan) {
                    $newDialplan = $originalDialplan->replicate();
                    $newDialplan->dialplan_uuid = Str::uuid();
                    $newDialplan->dialplan_description .= '(Copy)';
                    $newDialplan->save();

                    foreach ($originalDialplan->dialplanDetails as $detail) {
                        $newDetail = $detail->replicate();
                        $newDetail->dialplan_uuid = $newDialplan->dialplan_uuid;
                        $newDetail->dialplan_detail_uuid = Str::uuid();
                        $newDetail->save();
                    }
                }
            }

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');

            session()->flash('message', 'Time Conditions copied successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred while copying Time Conditions: ' . $e->getMessage());
        }
    }





    public function builder(): Builder
    {
        $appUuid = config('fusionpbx.time_conditions.app_uuid');

        $query = Dialplan::query()
            ->where('app_uuid', $appUuid);
        if ($this->showAll) {
            $query->leftJoin('v_domains', 'v_dialplans.domain_uuid', '=', 'v_domains.domain_uuid')
                ->select('v_dialplans.*', 'v_domains.domain_name');
        } else {
            $query->where(function ($query) {
                $query->where('v_dialplans.domain_uuid', auth()->user()->domain_uuid)
                    ->orWhereNull('v_dialplans.domain_uuid');
            });
        }

        return $query;

    }
}
