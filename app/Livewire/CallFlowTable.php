<?php

namespace App\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\CallFlow;
use App\Models\Dialplan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CallFlowTable extends DataTableComponent
{
    protected $model = CallFlow::class;
    public bool $showAll = false;

    public function configure(): void
    {
        $canEdit = auth()->user()->hasPermission("call_flow_edit");
        $tableConfig = $this->setPrimaryKey('call_flow_uuid')
            ->setTableAttributes([
                'class' => 'table table-striped table-hover table-bordered'
            ])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search time conditions')
            ->setPerPageAccepted([10, 25, 50, 100])
            ->setPaginationEnabled();
        if ($canEdit) {
            $tableConfig->setTableRowUrl(function ($row) use ($canEdit) {
                return route('call_flows.edit', $row->call_flow_uuid);
            });
        }

        if (request('showAll')) {
            $this->showAll = true;
        }
    }

    public function columns(): array
    {
        $columns = [
            Column::make("Call flow uuid", "call_flow_uuid")
                ->sortable()
                ->hideIf(true),
            Column::make("Domain uuid", "domain_uuid")
                ->sortable()
                ->hideIf(true),
            Column::make("Name", "call_flow_name")
                ->sortable()
                ->searchable(),
            Column::make("Extension", "call_flow_extension")
                ->sortable()
                ->searchable(),
            Column::make("Feature code", "call_flow_feature_code")
                ->sortable()
                ->searchable(),
            Column::make("Context", "call_flow_context")
                ->sortable()
                ->searchable(),
            Column::make("Status", "call_flow_status")
                ->sortable()
                ->searchable(),
            Column::make("Enabled", "call_flow_enabled")
                ->sortable()
                ->searchable(),
            Column::make("Description", "call_flow_description")
                ->sortable(),
        ];

        if ($this->showAll) {
            $columns[] = Column::make('Domain name', 'domain.domain_name')
                ->searchable()
                ->sortable();
        }

        return $columns;
    }

    public function bulkActions(): array
    {
        $bulkActions = [];

        if (auth()->user()->hasPermission('call_flow_delete')) {
            $bulkActions['bulkDelete'] = 'Delete';
        }

        if (auth()->user()->hasPermission('call_flow_add')) {
            $bulkActions['bulkCopy'] = 'Copy';
        }

        if (auth()->user()->hasPermission('call_flow_edit')) {
            $bulkActions['bulkToggle'] = 'Toggle';
        }
        return $bulkActions;
    }

    public function bulkToggle()
    {
        $selectRows = $this->getSelected();

        CallFlow::whereIn('call_flow_uuid', $selectRows)
            ->update([
                'call_flow_enabled' => DB::raw("CASE WHEN call_flow_enabled = 'true' THEN 'false' ELSE 'true' END")
            ]);

        $this->clearSelected();
        $this->dispatch('refresh');

        session()->flash('message', 'Call Flow status toggled successfully');
    }

    public function bulkDelete()
    {
        $selectRows = $this->getSelected();

        try {
            DB::beginTransaction();

            CallFlow::whereIn('call_flow_uuid', $selectRows)->delete();

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');

            session()->flash('message', 'Devices deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function bulkCopy()
    {
        $selectRows = $this->getSelected();

        try {
            DB::beginTransaction();

            $callFlows = CallFlow::whereIn('call_flow_uuid', $selectRows)->get();

            foreach ($callFlows as $callFlow) {
                $newCallFlow = $callFlow->replicate();
                $newCallFlow->call_flow_uuid = Str::uuid()->toString();
                $newCallFlow->call_flow_name = $newCallFlow->call_flow_name . ' (Copy)';
                $newCallFlow->save();
            }

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');

            session()->flash('message', 'Call Flows copied successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function builder(): Builder
    {
        $query = CallFlow::query();

        if ($this->showAll) {
            $query->leftJoin('v_domains', 'v_call_flows.domain_uuid', '=', 'v_domains.domain_uuid')
                ->select('v_call_flows.*', 'v_domains.domain_name');
        } else {
            $query->where(function ($query) {
                $query->where('v_call_flows.domain_uuid', auth()->user()->domain_uuid)
                    ->orWhereNull('v_call_flows.domain_uuid');
            });
        }

        return $query;
    }
}
