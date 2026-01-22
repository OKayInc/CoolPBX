<?php

namespace App\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\DomainSetting;

class DomainSettingTable extends DataTableComponent
{
    protected $model = DomainSetting::class;

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

        // if ($canEdit) {
        //     $tableConfig->setTableRowUrl(function ($row) use ($canEdit) {
        //         return route('call_flows.edit', $row->domain_setting_uuid);
        //     });
        // }

    }

    public function columns(): array
    {
        $columns = [
            Column::make("Domain setting uuid", "domain_setting_uuid")
                ->sortable()
                ->hideIf(true),
            Column::make("Category", "domain_setting_category")
                ->sortable(),
            Column::make("Subcategory", "domain_setting_subcategory")
                ->sortable(),
            Column::make("Type", "domain_setting_name")
                ->sortable(),
            Column::make("Value", "domain_setting_value")
                ->sortable(),
            Column::make("Enabled", "domain_setting_enabled")
                ->sortable(),
            Column::make("Description", "domain_setting_description")
                ->sortable(),
        ];
        
        return $columns;
    }
}
