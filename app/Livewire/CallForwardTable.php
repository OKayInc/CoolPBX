<?php

namespace App\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\Extension;
use App\Models\FollowMe;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class CallForwardTable extends DataTableComponent
{
    protected $model = Extension::class;
    public bool $show_all = false;

    public function configure(): void
    {
        $canEdit = auth()->user()->hasPermission('call_forward_all');
        $tableConfig = $this->setPrimaryKey('extension_uuid')
            ->setTableAttributes([
                'class' => 'table table-striped table-hover table-bordered'
            ])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search Call Forward')
            ->setPerPageAccepted([10, 25, 50, 100])
            ->setPaginationEnabled();
            
        if ($canEdit) {
            $tableConfig->setTableRowUrl(function ($row) use ($canEdit){
                return route('call_forward.edit', $row->extension_uuid);
            });
        }

        if(request('show_all')) {
            $this->show_all = true;
        }
    }

    public function columns(): array
    {
        $columns = [];
        
        $columns = [
            Column::make("Extension uuid", "extension_uuid")
                ->hideIf(true),

            Column::make("Extension", "extension")
                ->sortable()
                ->searchable(),

            Column::make('Call Forward', 'forward_all_destination')
                ->format(function ($value, $row) {
                    if ($row->forward_all_enabled === 'true' || $row->forward_all_enabled === true) {
                        return $value ? format_phone($value) : '<span class="text-muted">(Invalid)</span>';
                    }
                    return '<span class="text-muted">Disabled</span>';
                })
                ->html()
                ->sortable(),

            Column::make("Follow Me", "follow_me_enabled")
                ->format(function ($value, $row) {
                    if ($row->follow_me_enabled === 'true' || $row->follow_me_enabled === true) {
                        $count = $row->followMe?->destinations?->count() ?? 0;
                        if ($count > 0) {
                            return "Enabled ({$count})";
                        }
                        return '<span class="text-muted">(Invalid)</span>';
                    }
                    return '&nbsp;';
                })
                ->html()
                ->sortable(),

            Column::make("Do not Disturb", "do_not_disturb")
                ->format(function ($value) {
                    return ($value === 'true' || $value === true) ? 'Enabled' : '&nbsp;';
                })
                ->html()
                ->sortable(),

            Column::make("Description", "description")
                ->sortable()
                ->searchable(),
        ];

        if($this->show_all){
            array_splice($columns, 3, 0, [
                Column::make('Domain', 'domain.domain_name')
                    ->searchable()
                    ->sortable()
            ]);
        }

        return $columns;
    }

    public function bulkActions(): array
    {
        $bulkActions = [];

        if (auth()->user()->hasPermission('call_forward')) {
            $bulkActions['bulkCallForward'] = 'Call Forward';
        }

        if (auth()->user()->hasPermission('follow_me')) {
            $bulkActions['bulkFollowMe'] = 'Follow Me';
        }

        if (auth()->user()->hasPermission('do_not_disturb')) {
            $bulkActions['bulkDoNotDisturb'] = 'Do Not Disturb';
        }

        return $bulkActions;
    }


    public function bulkCallForward()
    {
        $selectedRows = $this->getSelected();

        if (empty($selectedRows)) {
            session()->flash('error', 'No extensions selected');
            return;
        }

        try {
            DB::beginTransaction();

            $extensions = Extension::whereIn('extension_uuid', $selectedRows)
                ->where('enabled', 'true')
                ->get();

            $updatedCount = 0;

            foreach ($extensions as $extension) {
                $currentState = $extension->forward_all_enabled === 'true';
                $newState = !$currentState;

                if ($newState && empty($extension->forward_all_destination)) {
                    continue; 
                }

                $updateData = [
                    'forward_all_enabled' => $newState ? 'true' : 'false'
                ];

                if ($newState) {
                    $updateData['follow_me_enabled'] = 'false';
                    $updateData['do_not_disturb'] = 'false';

                    if ($extension->follow_me_uuid) {
                        FollowMe::where('follow_me_uuid', $extension->follow_me_uuid)
                            ->update(['follow_me_enabled' => 'false']);
                    }
                }

                $extension->update($updateData);
                $updatedCount++;

                // TODO: Implementar notificaciones a teléfonos si es necesario
                // $this->sendFeatureEventNotify($extension);
            }

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');

            if ($updatedCount > 0) {
                session()->flash('message', "Call Forward toggled for {$updatedCount} extension(s)");
            } else {
                session()->flash('warning', 'No extensions were updated. Check if destinations are configured.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error toggling Call Forward: ' . $e->getMessage());
        }
    }


    public function bulkFollowMe()
    {
        $selectedRows = $this->getSelected();

        if (empty($selectedRows)) {
            return session()->flash('error', 'No extensions selected');
            
        }

        try {
            DB::beginTransaction();

            $extensions = Extension::with('followMe.destinations')
                ->whereIn('extension_uuid', $selectedRows)
                ->where('enabled', 'true')
                ->get();

            $updatedCount = 0;
            $skippedCount = 0;

            foreach ($extensions as $extension) {
                $currentState = $extension->follow_me_enabled === 'true';
                
                $destinationsExist = false;
                if (!$currentState && $extension->follow_me_uuid) {
                    $destinationsCount = $extension->followMe?->destinations?->count() ?? 0;
                    $destinationsExist = $destinationsCount > 0;
                }

                $newState = !$currentState && $destinationsExist ? true : ($currentState ? false : false);

                if ($newState === $currentState) {
                    if (!$currentState && !$destinationsExist) {
                        $skippedCount++;
                    }
                    continue;
                }

                $updateData = [
                    'follow_me_enabled' => $newState ? 'true' : 'false'
                ];

                if ($newState) {
                    $updateData['forward_all_enabled'] = 'false';
                    $updateData['do_not_disturb'] = 'false';
                }

                $extension->update($updateData);

                if ($extension->follow_me_uuid) {
                    FollowMe::where('follow_me_uuid', $extension->follow_me_uuid)
                        ->update(['follow_me_enabled' => $newState ? 'true' : 'false']);
                }

                $updatedCount++;

                // TODO::Implementar notificaciones a teléfonos si es necesario se debe crear un service
                // $this->sendFeatureEventNotify($extension);


            }

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');

            $messages = [];
            if ($updatedCount > 0) {
                $messages[] = "Follow Me toggled for {$updatedCount} extension(s)";
            }
            if ($skippedCount > 0) {
                $messages[] = "{$skippedCount} extension(s) skipped - no destinations configured";
            }

            if (!empty($messages)) {
                session()->flash('message', implode('. ', $messages));
            } else {
                session()->flash('warning', 'No extensions were updated');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error toggling Follow Me: ' . $e->getMessage());
        }
    }


    public function bulkDoNotDisturb()
    {
        $selectedRows = $this->getSelected();

        if (empty($selectedRows)) {
            session()->flash('error', 'No extensions selected');
            return;
        }

        try {
            DB::beginTransaction();

            $extensions = Extension::whereIn('extension_uuid', $selectedRows)
                ->where('enabled', 'true')
                ->get();

            $updatedCount = 0;

            foreach ($extensions as $extension) {
                $currentState = $extension->do_not_disturb === 'true';
                $newState = !$currentState;

                $updateData = [
                    'do_not_disturb' => $newState ? 'true' : 'false'
                ];

                if ($newState) {
                    $updateData['forward_all_enabled'] = 'false';
                    $updateData['follow_me_enabled'] = 'false';

                    if ($extension->follow_me_uuid) {
                        FollowMe::where('follow_me_uuid', $extension->follow_me_uuid)
                            ->update(['follow_me_enabled' => 'false']);
                    }
                }

                $extension->update($updateData);
                $updatedCount++;

                // TODO: Implementar notificaciones a teléfonos si es necesario
                // $this->sendFeatureEventNotify($extension);


            }

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');

            session()->flash('message', "Do Not Disturb toggled for {$updatedCount} extension(s)");

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error toggling Do Not Disturb: ' . $e->getMessage());
        }
    }

    public function builder(): Builder
    {
        $query = Extension::query();

        if ($this->show_all) {
            $query->leftJoin('v_domains', 'v_extensions.domain_uuid', '=', 'v_domains.domain_uuid')
                  ->select('v_extensions.*', 'v_domains.domain_name');
        } else {
            $query->where(function ($query) {
                $query->where('v_extensions.domain_uuid', Session::get('domain_uuid'))
                    ->orWhereNull('v_extensions.domain_uuid');
            });
        }

        return $query;
    }
}