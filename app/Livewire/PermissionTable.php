<?php

namespace App\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\Permission;

class PermissionTable extends DataTableComponent
{
    protected $model = Permission::class;

    public function configure(): void
    {
        $this->setPrimaryKey('id');
    }

    public function columns(): array
    {
        return [
            Column::make("Permission uuid", "permission_uuid")
                ->sortable(),
            Column::make("Application uuid", "application_uuid")
                ->sortable(),
            Column::make("Application name", "application_name")
                ->sortable(),
            Column::make("Permission name", "permission_name")
                ->sortable(),
            Column::make("Permission description", "permission_description")
                ->sortable(),
            Column::make("Created at", "created_at")
                ->sortable(),
            Column::make("Updated at", "updated_at")
                ->sortable(),
        ];
    }
}
