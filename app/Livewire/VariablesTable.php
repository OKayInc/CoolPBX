<?php

namespace App\Livewire;

use App\Models\Variable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;

class VariablesTable extends DataTableComponent
{
    public $lastCategory = null;
    public $zebraIndex = 0;

    public function zebraRowAttributes($row): array
    {
        if($this->lastCategory !== $row->var_category)
        {
            $this->zebraIndex = $this->zebraIndex ? 0 : 1;
            $this->lastCategory = $row->var_category;
        }

        return [
            'class' => $this->zebraIndex ? 'table-secondary' : 'table-light'
        ];
    }

    public function configure(): void
    {
        $canEdit = auth()->user()->hasPermission('var_edit');

        $this->setPrimaryKey('var_uuid')
            ->setTableAttributes([
                'class' => 'table table-hover table-bordered'
            ])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search Variables')
            ->setPerPageAccepted([10, 25, 50, 100, 250])
            ->setDefaultPerPage(100)
            ->setTableRowUrl(fn($row) =>
                $canEdit ? route('variables.edit', $row->var_uuid) : null
            )
            ->setTrAttributes(fn($row) => $this->zebraRowAttributes($row));
    }

    public function bulkActions(): array
    {
        $bulkActions = [];

        if (auth()->user()->hasPermission('var_edit')) {
            $bulkActions['markEnabled'] = 'Mark as Enabled';
            $bulkActions['markDisabled'] = 'Mark as Disabled';
        }

        if (auth()->user()->hasPermission('var_delete')) {
            $bulkActions['bulkDelete'] = 'Delete';
        }

        if(auth()->user()->hasPermission('var_add')) {
            $bulkActions['bulkCopy'] = 'Copy';
        }

        return $bulkActions;
    }

    public function markEnabled()
    {
        if (!auth()->user()->hasPermission('var_edit')) {
            session()->flash('error', 'You do not have permission to mark variables as enabled.');
            return;
        }

        $selectedRows = $this->getSelected();

        Variable::whereIn('var_uuid', $selectedRows)->update(['var_enabled' => 'true']);

        $this->clearSelected();
        $this->dispatch('refresh');
        session()->flash('success', 'The variables were successfully enabled.');
    }

    public function markDisabled()
    {
        if (!auth()->user()->hasPermission('var_edit')) {
            session()->flash('error', 'You do not have permission to mark variables as disabled.');
            return;
        }

        $selectedRows = $this->getSelected();

        Variable::whereIn('var_uuid', $selectedRows)->update(['var_enabled' => 'false']);

        $this->clearSelected();
        $this->dispatch('refresh');
        session()->flash('success', 'The variables were successfully disabled.');
    }

    public function bulkDelete()
    {
        if (!auth()->user()->hasPermission('var_delete')) {
            session()->flash('error', 'You do not have permission to delete variables.');
            return;
        }

        $selectedRows = $this->getSelected();

        try {
            DB::beginTransaction();

            Variable::whereIn('var_uuid', $selectedRows)->delete();

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');
            session()->flash('success', 'Variables successfully deleted.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'There was a problem deleting the variables: ' . $e->getMessage());
        }
    }

    public function bulkCopy()
    {
        if (!auth()->user()->hasPermission('var_add')) {
            session()->flash('error', 'You do not have permission to copy variables.');
            return;
        }

        $selectedRows = $this->getSelected();

        try {
            DB::beginTransaction();

            foreach ($selectedRows as $variableUuid) {
                $originalVariable = Variable::findOrFail($variableUuid);

                $newVariable = $originalVariable->replicate();
                $newVariable->var_uuid = Str::uuid();
                $newVariable->var_name = $originalVariable->var_name . ' (Copy)';
                $newVariable->save();
            }

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
            session()->flash('error', 'There was a problem copying the variables: ' . $e->getMessage());
        }
    }

    public function columns(): array
    {
        return [

            Column::make("UUID", "var_uuid")->hideIf(true),

            Column::make("Category", "var_category")
                ->searchable(),

            Column::make("Name", "var_name")
                ->searchable(),

            BooleanColumn::make("Enabled", "var_enabled")
        ];
    }

    public function builder(): Builder
    {
        $query = Variable::query()
            ->orderBy('var_category', 'asc')
            ->orderBy('var_name', 'asc');

	    if(App::hasDebugModeEnabled())
        {
            Log::notice('['.__FILE__.':'.__LINE__.']['.__CLASS__.']['.__METHOD__.'] query: '.$query->toRawSql());
        }

        return $query;
    }
}
