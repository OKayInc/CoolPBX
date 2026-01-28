<?php

namespace App\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\ExtensionSetting;
use Illuminate\Database\Eloquent\Builder;

class ExtensionSettingTable extends DataTableComponent
{
    protected $model = ExtensionSetting::class;

    public string $extensionUuid;

    public function configure(): void
    {
        $canEdit = auth()->user()->can('extension_setting_edit');
        $tableConfig = $this->setPrimaryKey('extension_setting_uuid')
            ->setTableAttributes([
                'class' => 'table table-striped table-hover table-bordered'
            ])
            ->setSearchEnabled()
            ->setSearchPlaceholder('Search call center agents')
            ->setPerPageAccepted([10, 25, 50, 100])
            ->setPaginationEnabled();

        if ($canEdit) {
            $tableConfig->setTableRowUrl(function ($row) use ($canEdit) {
                return route('extensions.settings.edit', $row->extension_setting_uuid);
            });
        }
    }

    public function columns(): array
    {
        return [
            Column::make("Extension setting uuid", "extension_setting_uuid")
                ->sortable()
                ->hideIf(true),
            Column::make("Domain uuid", "domain_uuid")
                ->sortable()
                ->hideIf(true),
            Column::make("Extension uuid", "extension_uuid")
                ->sortable()
                ->hideIf(true),
            Column::make("Type", "extension_setting_type")
                ->sortable(),
            Column::make("Name", "extension_setting_name")
                ->sortable(),
            Column::make("Value", "extension_setting_value")
                ->sortable(),
            Column::make("Enabled", "extension_setting_enabled")
                ->sortable(),
            Column::make("Description", "extension_setting_description")
                ->sortable(),
        ];
    }

    public function builder(): Builder
    {
        return ExtensionSetting::query()
                ->where('extension_uuid', $this->extensionUuid);
    }
}
