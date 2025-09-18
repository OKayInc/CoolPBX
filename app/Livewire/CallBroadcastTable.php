<?php

namespace App\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\CallBroadcast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CallBroadcastTable extends DataTableComponent
{
    protected $model = CallBroadcast::class;
    public bool $show_all = false;

    public function configure(): void
    {
        $canEdit = auth()->user()->hasPermission('call_broadcast_edit');
        $tableConfig = $this->setPrimaryKey('call_broadcast_uuid')
            ->setTableAttributes([
                'class' => 'table table-striped table-hover table-bordered'
            ])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search devices')
            ->setPerPageAccepted([10, 25, 50, 100])
            ->setPaginationEnabled();

        if ($canEdit) {
            $tableConfig->setTableRowUrl(function ($row) use ($canEdit) {
                return route('call_broadcasts.edit', $row->call_broadcast_uuid);
            });
        }

        if(request('show_all'))
        {
            $this->show_all = true;
        }
    }

    public function columns(): array
    {
        $columns = [
            Column::make("Call broadcast uuid", "call_broadcast_uuid")
                ->sortable()
                ->hideIf(true),
            Column::make("Domain uuid", "domain_uuid")
                ->sortable()
                ->hideIf(true),
            Column::make("Broadcast name", "broadcast_name")
                ->sortable()
                ->searchable(),
            Column::make("Concurrent limit", "broadcast_concurrent_limit")
                ->sortable()
                ->searchable(),
            Column::make("Start time", "broadcast_start_time")
                ->sortable()
                ->searchable(),
            Column::make("Description", "broadcast_description")
                ->sortable()
                ->searchable(),
        ];

        if ($this->show_all) {
            array_splice($columns, 3, 0, [
                Column::make("Domain", "domain.domain_name")
                    ->sortable()
                    ->searchable()
            ]);
        }
        
        return $columns;
    }

    public function bulkActions(): array
    {
        $bulkActions = [];

        if(auth()->user()->hasPermission('call_broadcast_delete'))
        {
            $bulkActions['bulkDelete'] = 'Delete';
        }

        if(auth()->user()->hasPermission('call_broadcast_add'))
        {
            $bulkActions['bulkCopy'] = 'Copy';
        }
        return $bulkActions;
    }

    public function bulkDelete()
    {
        $selectedRows = $this->getSelected();

        try
        {
            DB::beginTransaction();

            CallBroadcast::whereIn('call_broadcast_uuid', $selectedRows)->delete();

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');

            session()->flash('message', 'Call Broadcast(s) deleted sucessfully');
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

            $callBroadcasts = CallBroadcast::whereIn('call_broadcast_uuid', $selectRows)->get();

            foreach ($callBroadcasts as $callBroadcast) {
                $newCallBroadcast = $callBroadcast->replicate();
                $newCallBroadcast->call_broadcast_uuid = \Str::uuid()->toString();
                $newCallBroadcast->broadcast_name = $callBroadcast->broadcast_name . ' - Copy';
                $newCallBroadcast->save();
            }

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');

            session()->flash('message', 'Call Broadcast(s) copied successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function builder(): Builder
    {
        $query = CallBroadcast::query();

        if($this->show_all)
        {
            $query->leftJoin('v_domains', 'v_call_broadcasts.domain_uuid', '=', 'v_domains.domain_uuid')
                ->select('v_call_broadcasts.*', 'v_domains.domain_name');
        } else {
            $query->where(function ($query) {
                $query->where('v_call_broadcasts.domain_uuid', auth()->user()->domain_uuid)
                    ->orWhereNull('v_call_broadcasts.domain_uuid');
            });
        }
        return $query;
    }
}
