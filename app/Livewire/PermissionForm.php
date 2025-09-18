<?php

namespace App\Livewire;

use Livewire\Component;
use App\Repositories\PermissionRepository;
use App\Models\Permission;
use App\Models\Group;
use App\Models\GroupPermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionForm extends Component
{
    public ?string $permissionUuid = null;
    public $permission;
    public $isEditing = false;

    // Permission fields
    public ?string $application_uuid = null;
    public string $application_name = '';
    public string $permission_name = '';
    public ?string $permission_description = '';

    // Group assignment
    public array $selectedGroups = [];
    public array $groupPermissions = [];

    // Available data
    public array $availableGroups = [];
    public array $availableApplications = [];

    protected $permissionRepository;

    public function boot(PermissionRepository $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    public function rules()
    {
        return [
            'application_name' => 'required|string|max:255',
            'permission_name' => [
                'required',
                'string',
                'max:255',
                'unique:v_permissions,permission_name' . ($this->isEditing ? ',' . $this->permissionUuid . ',permission_uuid' : ''),
            ],
            'permission_description' => 'nullable|string|max:500',
            'selectedGroups' => 'array',
            'selectedGroups.*' => 'exists:v_groups,group_uuid',
        ];
    }

    public function mount($permissionUuid = null)
    {
        $this->permissionUuid = $permissionUuid;
        $this->isEditing = !is_null($permissionUuid);

        $this->loadDropdownData();

        if ($this->isEditing) {
            $this->loadPermission();
        } else {
            $this->initializeDefaults();
        }
    }

    protected function loadPermission()
    {
        $this->permission = Permission::with(['groupPermissions.group'])
            ->where('permission_uuid', $this->permissionUuid)
            ->first();

        if (!$this->permission) {
            session()->flash('error', 'Permission not found.');
            return redirect()->route('permissions.index');
        }

        $this->application_uuid = $this->permission->application_uuid;
        $this->application_name = $this->permission->application_name;
        $this->permission_name = $this->permission->permission_name;
        $this->permission_description = $this->permission->permission_description;

        $this->selectedGroups = $this->permission->groupPermissions
            ->where('permission_assigned', 'true')
            ->pluck('group_uuid')
            ->toArray();

        foreach ($this->permission->groupPermissions as $groupPermission) {
            // dd($groupPermission);
            $this->groupPermissions[$groupPermission->group_uuid] = [
                'assigned' => $groupPermission->permission_assigned === true,
                'protected' => $groupPermission->permission_protected === true,
            ];
        }
    }

    protected function initializeDefaults()
    {
        $this->selectedGroups = [];
        $this->groupPermissions = [];
    }

    protected function loadDropdownData()
    {
        $user = auth()->user();
        
        $this->availableGroups = Group::where(function($query) use ($user) {
                $query->where('domain_uuid', $user->domain_uuid)
                      ->orWhereNull('domain_uuid');
            })
            ->orderBy('group_name')
            ->get()
            ->toArray();

        $this->availableApplications = Permission::select('application_name')
            ->distinct()
            ->orderBy('application_name')
            ->pluck('application_name')
            ->toArray();
    }

    public function updatedSelectedGroups($value)
    {
        foreach ($this->selectedGroups as $groupUuid) {
            if (!isset($this->groupPermissions[$groupUuid])) {
                $this->groupPermissions[$groupUuid] = [
                    'assigned' => true,
                    'protected' => false,
                ];
            }
        }

        $this->groupPermissions = array_intersect_key(
            $this->groupPermissions, 
            array_flip($this->selectedGroups)
        );
    }

    public function toggleGroupProtection($groupUuid)
    {
        if (isset($this->groupPermissions[$groupUuid])) {
            $this->groupPermissions[$groupUuid]['protected'] = 
                !$this->groupPermissions[$groupUuid]['protected'];
        }
    }

    public function save()
    {
        $this->validate();

        DB::beginTransaction();
        
        try {
            $permissionData = [
                'application_uuid' => $this->application_uuid ?: Str::uuid()->toString(),
                'application_name' => $this->application_name,
                'permission_name' => $this->permission_name,
                'permission_description' => $this->permission_description,
            ];

            if ($this->isEditing) {
                $permission = Permission::where('permission_uuid', $this->permissionUuid)->first();
                $permission->update($permissionData);
            } else {
                $permissionData['permission_uuid'] = Str::uuid()->toString();
                $permission = Permission::create($permissionData);
                $this->permissionUuid = $permission->permission_uuid;
            }

            $this->saveGroupAssignments($permission);

            DB::commit();

            session()->flash('success', $this->isEditing ? 'Permission updated successfully.' : 'Permission created successfully.');
            return redirect()->route('permissions.edit', $permission->permission_uuid);
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error saving permission: ' . $e->getMessage());
        }
    }

    protected function saveGroupAssignments($permission)
    {
        if ($this->isEditing) {
            GroupPermission::where('permission_name', $permission->permission_name)->delete();
        }

        foreach ($this->selectedGroups as $groupUuid) {
            $group = Group::where('group_uuid', $groupUuid)->first();
            
            GroupPermission::create([
                'group_permission_uuid' => Str::uuid()->toString(),
                'domain_uuid' => $group->domain_uuid,
                'group_uuid' => $groupUuid,
                'permission_name' => $permission->permission_name,
                'permission_assigned' => 'true',
                'permission_protected' => $this->groupPermissions[$groupUuid]['protected'] ? 'true' : 'false',
                'group_name' => $group->group_name,
            ]);
        }
    }

    public function delete()
    {
        if (!$this->isEditing) {
            return;
        }

        DB::beginTransaction();
        
        try {
            GroupPermission::where('permission_name', $this->permission->permission_name)->delete();
            
            Permission::where('permission_uuid', $this->permissionUuid)->delete();
            
            DB::commit();
            
            session()->flash('success', 'Permission deleted successfully.');
            return redirect()->route('permissions.index');
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error deleting permission: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.permission-form');
    }
}