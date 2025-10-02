<?php

namespace App\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\CallCenterQueue;
use App\Models\CallCenterTier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class CallCenterQueueTable extends DataTableComponent
{
    protected $model = CallCenterQueue::class;
    public bool $showAll = false;

    public function configure(): void
    {
        $canEdit = auth()->user()->hasPermission('call_center_queue_edit');

        $tableConfig = $this->setPrimaryKey('call_center_queue_uuid')
            ->setTableAttributes([
                'class' => 'table table-striped table-hover table-bordered'
            ])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search call center queues')
            ->setPerPageAccepted([10, 25, 50, 100])
            ->setPaginationEnabled();

        if ($canEdit) {
            $tableConfig->setTableRowUrl(function ($row) use ($canEdit) {
                return route('call_center_queues.edit', $row->call_center_queue_uuid);
            });
        }

        if (request('show_all')) {
            $this->showAll = true;
        }
    }

    public function columns(): array
    {
        $columns = [];
        $columns =  [
            Column::make("Call center queue uuid", "call_center_queue_uuid")
                ->sortable()
                ->searchable()
                ->hideIf(true),
            Column::make("Domain uuid", "domain_uuid")
                ->sortable()
                ->searchable()
                ->hideIf(true),
            Column::make("Queue name", "queue_name")
                ->sortable()
                ->searchable(),
            Column::make("Extension", "queue_extension")
                ->sortable()
                ->searchable(),
            Column::make("Strategy", "queue_strategy")
                ->sortable()
                ->searchable(),
            Column::make("Tier rules apply", "queue_tier_rules_apply")
                ->sortable()
                ->searchable(),
            Column::make("Description", "queue_description")
                ->sortable()
                ->searchable(),

            Column::make("Priority", "queue_time_base_score_sec")
                ->sortable()
                ->format(function ($value) {
                    if (empty($value) || $value == 0) {
                        return '<span class="badge bg-secondary">Default</span>';
                    }

                    $priority = match (true) {
                        $value <= 60 => '<span class="badge bg-danger">Highest</span>',
                        $value <= 120 => '<span class="badge bg-warning">High</span>',
                        $value <= 300 => '<span class="badge bg-info">Medium</span>',
                        $value > 300 => '<span class="badge bg-success">Normal</span>',
                    };

                    return $priority . ' <small class="text-muted">(' . $value . 's)</small>';
                })
                ->html(),
        ];

        if ($this->showAll) {
            $columns[] = Column::make("Domain name", "domain.domain_name")
                ->sortable()
                ->searchable();
        }

        return $columns;
    }

    public function bulkActions(): array
    {
        $actions = [];
        if (auth()->user()->hasPermission('call_center_queue_delete')) {
            $actions['bulkDelete'] = 'Delete';
        }

        if (auth()->user()->hasPermission('call_center_queue_add')) {
            $actions['bulkCopy'] = 'Copy';
        }

        return $actions;
    }

    public function bulkDelete()
    {
        $selectRows = $this->getSelected();

        try {
            DB::beginTransaction();
            CallCenterQueue::whereIn('call_center_queue_uuid', $selectRows)->delete();
            CallCenterTier::whereIn('call_center_queue_uuid', $selectRows)->delete();

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');
        } catch (\Throwable $th) {
            throw $th;
        }
    } 

    public function bulkCopy()
    {
        $selectRows = $this->getSelected();

        try {
            DB::beginTransaction();

            $queues = CallCenterQueue::whereIn('call_center_queue_uuid', $selectRows)
                ->with('callcenteragents') 
                ->get();



            foreach ($queues as $queue) {
                $newQueue = $queue->replicate();
                $newQueue->call_center_queue_uuid = Str::uuid()->toString();
                $newQueue->queue_description = $newQueue->queue_description . ' (Copy)';
                $newQueue->save();

                foreach ($queue->callcenteragents as $agent) {
                    $pivot = $agent->pivot;

                    $newTier = $pivot->replicate();
                    $newTier->call_center_tier_uuid = Str::uuid()->toString();
                    $newTier->call_center_queue_uuid = $newQueue->call_center_queue_uuid;
                    $newTier->call_center_agent_uuid = $agent->call_center_agent_uuid;
                    $newTier->save();
                }
            }

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function filters(): array
    {
        return [
            SelectFilter::make('Priority Level')
                ->options([
                    '' => 'All',
                    'highest' => 'Highest',
                    'high' => 'High',
                    'medium' => 'Medium',
                    'normal' => 'Normal',
                    'not_set' => 'Not Set',
                ])
                ->filter(function (Builder $query, $value) {
                    if ($value === 'not_set') {
                        $query->where(function ($q) {
                            $q->whereNull('queue_time_base_score_sec')
                                ->orWhere('queue_time_base_score_sec', 0);
                        });
                    } else {
                        $ranges = [
                            'highest' => [1, 60],
                            'high' => [61, 120],
                            'medium' => [121, 300],
                            'normal' => [301, PHP_INT_MAX],
                        ];

                        if (isset($ranges[$value])) {
                            $query->whereBetween('queue_time_base_score_sec', $ranges[$value]);
                        }
                    }
                }),
        ];
    }

    public function builder(): Builder
    {
        $query = CallCenterQueue::query();

        if ($this->showAll) {
            $query->leftJoin("v_domains", "v_call_center_queues.domain_uuid", "=", "v_domains.domain_uuid")
                ->select("v_call_center_queues.*", "v_domains.domain_name");
        } else {
            $query->where(function ($query) {
                $query->where('v_call_center_queues.domain_uuid', auth()->user()->domain_uuid);
            });
        }

        return $query;
    }
}
