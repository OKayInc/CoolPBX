<?php

namespace App\Livewire;

use App\Models\IVRMenu;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;

class IvrMenuTable extends DataTableComponent
{
    protected $model = IVRMenu::class;

    public function configure(): void
    {
        $canEdit = auth()->user()->hasPermission('ivr_menu_edit');
        $this->setPrimaryKey('ivr_menu_uuid')
            ->setTableAttributes([
                'class' => 'table table-striped table-hover table-bordered'
            ])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search IVRMenus')
            ->setPerPageAccepted([10, 25, 50, 100, 250])
            ->setDefaultPerPage(100)
            ->setTableRowUrl(function ($row) use ($canEdit)
            {
                return $canEdit
                    ? route('ivr_menu.edit', $row->ivr_menu_uuid)
                    : null;
            })
            ->setPaginationEnabled();
    }

    public function bulkActions(): array
    {
        $bulkActions = [];

        if (auth()->user()->hasPermission('ivr_menu_edit'))
        {
            $bulkActions['markEnabled'] = 'Mark as Enabled';
            $bulkActions['markDisabled'] = 'Mark as Disabled';
        }

        if (auth()->user()->hasPermission('ivr_menu_delete'))
        {
            $bulkActions['bulkDelete'] = 'Delete';
        }

        if (auth()->user()->hasPermission('ivr_menu_add'))
        {
            $bulkActions['bulkCopy'] = 'Copy';
        }

        return $bulkActions;
    }

    public function markEnabled()
    {
        if (!auth()->user()->hasPermission('ivr_menu_edit'))
        {
            session()->flash('error', 'You do not have permission to mark ivr menu as enabled.');
            return;
        }

        $selectedRows = $this->getSelected();

        IVRMenu::whereIn('ivr_menu_uuid', $selectedRows)->update(['ivr_menu_enabled' => 'true']);

        $this->clearSelected();
        $this->dispatch('refresh');
        session()->flash('success', 'The ivr menu were successfully enabled.');
    }

    public function markDisabled()
    {
        if (!auth()->user()->hasPermission('ivr_menu_edit'))
        {
            session()->flash('error', 'You do not have permission to mark ivr menu as disabled.');
            return;
        }

        $selectedRows = $this->getSelected();

        IVRMenu::whereIn('ivr_menu_uuid', $selectedRows)->update(['ivr_menu_enabled' => 'false']);

        $this->clearSelected();
        $this->dispatch('refresh');
        session()->flash('success', 'The ivr menu were successfully disabled.');
    }


    public function bulkDelete()
    {
        if (!auth()->user()->hasPermission('ivr_menu_delete'))
        {
            session()->flash('error', 'You do not have permission to delete ivr menu.');
            return;
        }

        $selectedRows = $this->getSelected();

        try
        {
            DB::beginTransaction();

            IVRMenu::whereIn('ivr_menu_uuid', $selectedRows)->delete();

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');
            session()->flash('success', 'IVRMenus successfully deleted.');
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            session()->flash('error', 'There was a problem deleting the ivr menu: ' . $e->getMessage());
        }
    }

    public function bulkCopy()
    {
        if (!auth()->user()->hasPermission('ivr_menu_add'))
        {
            session()->flash('error', 'You do not have permission to copy ivr menu.');
            return;
        }

        $selectedRows = $this->getSelected();

        try
        {
            DB::beginTransaction();

            foreach ($selectedRows as $IVRMenuUuid)
            {
                $originalIVRMenu = IVRMenu::findOrFail($IVRMenuUuid);

                $newIVRMenu = $originalIVRMenu->replicate();
                $newIVRMenu->ivr_menu_uuid = Str::uuid();
                $newIVRMenu->ivr_menu_name = $newIVRMenu->ivr_menu_name . ' (Copy)';
                $newIVRMenu->save();
            }

            DB::commit();

            $this->clearSelected();
            $this->dispatch('refresh');
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            throw $e;
            session()->flash('error', 'There was a problem copying the ivr menu: ' . $e->getMessage());
        }
    }

    public function columns(): array
    {
        return [
            Column::make("IVRMenu uuid", "ivr_menu_uuid")->hideIf(true),

            Column::make("Name", "ivr_menu_name")
                ->sortable(),

            Column::make("Extension", "ivr_menu_extension")
                ->sortable(),

            BooleanColumn::make("Enabled", "ivr_menu_enabled")
                ->sortable(),

            Column::make("Description", "ivr_menu_description")
                ->sortable(),
        ];
    }

    public function builder(): Builder
    {
        $query = IVRMenu::query()->where('domain_uuid', Session::get('domain_uuid'));

        return $query;
    }
}
