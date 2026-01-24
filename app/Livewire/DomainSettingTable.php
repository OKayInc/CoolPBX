<?php

namespace App\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\DomainSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class DomainSettingTable extends DataTableComponent
{
    protected $model = DomainSetting::class;
    public string $domainUuid;

    public function mount(string $domainUuid)
    {
        $this->domainUuid = $domainUuid;
    }

    public function configure(): void
    {
        $canEdit = auth()->user()->hasPermission("domain_setting_edit");
        $tableConfig = $this->setPrimaryKey('domain_setting_uuid')
            ->setTableAttributes([
                'class' => 'table table-striped table-hover table-bordered'
            ])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search Domain Settings')
            ->setPerPageAccepted([10, 25, 50, 100])
            ->setPaginationEnabled();

        if ($canEdit) {
            $tableConfig->setTableRowUrl(function ($row) use ($canEdit) {
                return route('domains_settings.edit', [
                    'domainSettingUuid' => $row->domain_setting_uuid,
                    'domainUuid' => $this->domainUuid
                ]);
            });
        }
    }

    public function columns(): array
    {
        $columns = [
            Column::make("Domain setting uuid", "domain_setting_uuid")
                ->sortable()
                ->hideIf(true),
            Column::make("Category", "domain_setting_category")
                ->sortable()
                ->searchable(),
            Column::make("Subcategory", "domain_setting_subcategory")
                ->sortable()
                ->searchable(),
            Column::make("Type", "domain_setting_name")
                ->sortable()
                ->searchable(),
            Column::make("Value", "domain_setting_value")
                ->sortable()
                ->searchable(),
            Column::make("Enabled", "domain_setting_enabled")
                ->sortable(),
            Column::make("Description", "domain_setting_description")
                ->sortable()
                ->searchable(),
        ];

        return $columns;
    }

    public function bulkActions(): array
    {
        $bulkActions = [];

        if (auth()->user()->hasPermission('domain_setting_edit')) {
            $bulkActions['toggleDomainSetting'] = 'Toggle';
        }

        if (auth()->user()->hasPermission('domain_setting_delete')) {
            $bulkActions['bulkDelete'] = 'Delete';
        }

        if (auth()->user()->hasPermission('domain_setting_add')) {
            $bulkActions['bulkCopy'] = 'Copy';
        }

        return $bulkActions;
    }


    public function toggleDomainSetting()
    {
        if (!auth()->user()->hasPermission('domain_setting_edit')) {
            session()->flash('error', 'You do not have permission to toggle domain settings.');
            return;
        }

        $selectedRows = $this->getSelected();

        $settings = DomainSetting::whereIn('domain_setting_uuid', $selectedRows)->get();

        foreach ($settings as $setting) {
            $setting->domain_setting_enabled = $setting->domain_setting_enabled === 'true' ? 'false' : 'true';
            $setting->save();
        }

        $this->clearSelected();
        $this->dispatch('refresh');
        session()->flash('success', 'The domain settings were successfully toggled.');
    }

    public function bulkDelete()
    {
        if (!auth()->user()->hasPermission('domain_setting_delete')) {
            session()->flash('error', 'You do not have permission to delete domain settings.');
            return;
        }

        $selectedRows = $this->getSelected();

        DomainSetting::whereIn('domain_setting_uuid', $selectedRows)->delete();

        $this->clearSelected();
        $this->dispatch('refresh');
        session()->flash('success', 'The domain settings were successfully deleted.');
    }

    public function bulkCopy()
    {
        if (!auth()->user()->hasPermission('domain_setting_add')) {
            session()->flash('error', 'You do not have permission to copy domain settings.');
            return;
        }

        $selectedRows = $this->getSelected();

        $settings = DomainSetting::whereIn('domain_setting_uuid', $selectedRows)->get();

        foreach ($settings as $setting) {
            $newSetting = $setting->replicate();
            $newSetting->domain_setting_uuid = \Str::uuid()->toString();
            $newSetting->domain_setting_name = $newSetting->domain_setting_name . ' (Copy)';
            $newSetting->save();
        }

        $this->clearSelected();
        $this->dispatch('refresh');
        session()->flash('success', 'The domain settings were successfully copied.');
    }


    public function builder(): Builder
    {
        return DomainSetting::query()
            ->where('domain_uuid', $this->domainUuid)
            ->orderBy('domain_setting_category')
            ->orderBy('domain_setting_subcategory')
            ->orderBy('domain_setting_order');
    }
}
