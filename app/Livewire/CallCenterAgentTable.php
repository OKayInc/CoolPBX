<?php

namespace App\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\CallCenterAgent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CallCenterAgentTable extends DataTableComponent
{
    protected $model = CallCenterAgent::class;
    public bool $showAll = false;

    public function configure(): void
    {
        $canEdit = auth()->user()->hasPermission("call_center_agent_edit");

        $tableConfig = $this->setPrimaryKey('call_center_agent_uuid')
            ->setTableAttributes([
                'class' => 'table table-striped table-hover table-bordered'
            ])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search call center agents')
            ->setPerPageAccepted([10, 25, 50, 100])
            ->setPaginationEnabled();

        if ($canEdit) {
            $tableConfig->setTableRowUrl(function ($row) use ($canEdit) {
                return route('call_center_agent.edit', $row->call_center_agent_uuid);
            });
        }

        if (request('show_all')) {
            $this->showAll = true;
        }
    }

    public function columns(): array
    {
        $columns = [
            Column::make("Call center agent uuid", "call_center_agent_uuid")
                ->sortable()
                ->searchable()
                ->hideIf(true),
            Column::make("Domain uuid", "domain_uuid")
                ->sortable()
                ->searchable()
                ->hideIf(true),
            Column::make("Agent name", "agent_name")
                ->sortable()
                ->searchable(),
            Column::make("Agent id", "agent_id")
                ->sortable()
                ->searchable(),
            Column::make("Type", "agent_type")
                ->sortable()
                ->searchable(),
            Column::make("Call timeout", "agent_call_timeout")
                ->sortable()
                ->searchable(),
            Column::make("Agent contact", "agent_contact")
                ->sortable(),
            Column::make("Max no answer", "agent_max_no_answer")
                ->sortable()
                ->searchable(),
            Column::make('Default Status', 'agent_status')
                ->sortable()
                ->searchable(),
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
        $actions = [];
        if (auth()->user()->hasPermission('call_center_agent_delete')) {
            $actions['bulkDelete'] = 'Delete';
        }
        return $actions;
    }

    public function bulkDelete(): array
    {
        $selectRows = $this->getSelected();

        try {
            DB::beginTransaction();

            CallCenterAgent::whereIn('call_center_agent_uuid', $selectRows)->delete();

            DB::commit();

            $this->clearSearch();
            $this->dispatch('refresh');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function builder(): Builder
    {
        $query = CallCenterAgent::query();

        if ($this->showAll) {
            $query->leftJoin('v_domains', 'v_call_center_agents.domain_uuid', '=', 'v_domains.domain_uuid')
                ->select('v_call_center_agents.*', 'v_domains.domain_name');
        } else {
            $query->where(function ($query) {
                $query->where('v_call_center_agents.domain_uuid', auth()->user()->domain_uuid);
            });
        }
        return $query;
    }
}
