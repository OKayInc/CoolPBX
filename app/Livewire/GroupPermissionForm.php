<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Group;
use App\Models\Permission;
use App\Models\GroupPermission;
use App\Repositories\PermissionRepository;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class GroupPermissionForm extends Component
{
    public ?string $groupPermissionUuid = null;
    public $groupPermission;
    public $isEditing = false;

    // Form properties
    public ?string $domain_uuid = null;
    public ?string $group_uuid = null;
    public ?string $permission_name = '';
    public bool $permission_protected = false;
    public bool $permission_assigned = true;

    // Dropdown data
    public array $availableGroups = [];
    public array $availablePermissions = [];
    public array $availableDomains = [];

    // Additional properties for display
    public $selectedGroup = null;
    public $selectedPermission = null;

    protected $permissionRepository;

    public function boot(PermissionRepository $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    public function rules()
    {
        return [
            'domain_uuid' => 'required|string|exists:v_domains,domain_uuid',
            'group_uuid' => 'required|string|exists:v_groups,group_uuid',
            'permission_name' => [
                'required',
                'string',
                'exists:v_permissions,permission_name',
                Rule::unique('v_group_permissions')->where(function ($query) {
                    return $query->where('group_uuid', $this->group_uuid)
                                 ->where('permission_name', $this->permission_name);
                })->ignore($this->groupPermissionUuid, 'group_permission_uuid'),
            ],
            'permission_protected' => 'boolean',
            'permission_assigned' => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'domain_uuid.required' => 'Domain is required.',
            'group_uuid.required' => 'Group is required.',
            'permission_name.required' => 'Permission is required.',
            'permission_name.unique' => 'This permission is already assigned to the selected group.',
        ];
    }

    public function mount($groupPermissionUuid = null, $groupUuid = null)
    {
        $this->groupPermissionUuid = $groupPermissionUuid;
        $this->isEditing = !is_null($groupPermissionUuid);
        
        // If groupUuid is passed, pre-select it
        if ($groupUuid) {
            dd($groupUuid);
            $this->group_uuid = $groupUuid;
        }

        $this->loadDropdownData();

        if ($this->isEditing) {
            $this->loadGroupPermission();
        } else {
            $this->initializeDefaults();
        }
    }

    protected function loadGroupPermission()
    {
        $this->groupPermission = GroupPermission::with(['group', 'permission'])
            ->where('group_permission_uuid', $this->groupPermissionUuid)
            ->first();

        if (!$this->groupPermission) {
            session()->flash('error', 'Group Permission not found.');
            return redirect()->route('permissions.index');
        }

        $this->domain_uuid = $this->groupPermission->domain_uuid;
        $this->group_uuid = $this->groupPermission->group_uuid;
        $this->permission_name = $this->groupPermission->permission_name;
        $this->permission_protected = (bool) $this->groupPermission->permission_protected;
        $this->permission_assigned = (bool) $this->groupPermission->permission_assigned;

        $this->selectedGroup = $this->groupPermission->group;
        $this->selectedPermission = $this->groupPermission->permission;
    }

    protected function initializeDefaults()
    {
        $user = auth()->user();
        $this->domain_uuid = $user->domain_uuid ?? Session::get('domain_uuid');
        $this->permission_protected = false;
        $this->permission_assigned = true;
    }

    protected function loadDropdownData()
    {
        $user = auth()->user();
        $domainUuid = $user->domain_uuid ?? Session::get('domain_uuid');

        // Load available domains
        $this->availableDomains = collect([
            [
                'domain_uuid' => $domainUuid,
                'domain_name' => $user->domain->domain_name ?? 'Current Domain'
            ]
        ])->toArray();

        // Load groups for the domain
        $this->loadGroupsForDomain($domainUuid);

        // Load all permissions
        $this->loadAvailablePermissions();
    }

    protected function loadGroupsForDomain($domainUuid)
    {
        $this->availableGroups = Group::where('domain_uuid', $domainUuid)
            ->orWhereNull('domain_uuid') // Include global groups
            ->orderBy('group_name')
            ->get()
            ->map(function ($group) {
                return [
                    'group_uuid' => $group->group_uuid,
                    'group_name' => $group->group_name,
                    'full_name' => $group->full_group_name,
                    'is_global' => is_null($group->domain_uuid)
                ];
            })
            ->toArray();
    }

    protected function loadAvailablePermissions()
    {
        $this->availablePermissions = Permission::orderBy('application_name')
            ->orderBy('permission_name')
            ->get()
            ->groupBy('application_name')
            ->map(function ($permissions, $appName) {
                return [
                    'application_name' => $appName,
                    'permissions' => $permissions->map(function ($permission) {
                        return [
                            'permission_name' => $permission->permission_name,
                            'permission_description' => $permission->permission_description
                        ];
                    })->toArray()
                ];
            })
            ->toArray();
    }

    public function updatedGroupUuid()
    {
        if ($this->group_uuid) {
            $this->selectedGroup = Group::find($this->group_uuid);
        }
    }

    public function updatedPermissionName()
    {
        if ($this->permission_name) {
            $this->selectedPermission = Permission::where('permission_name', $this->permission_name)->first();
        }
    }

    public function save()
    {
        $this->validate();

        try {
            $data = [
                'domain_uuid' => $this->domain_uuid,
                'group_uuid' => $this->group_uuid,
                'permission_name' => $this->permission_name,
                'permission_protected' => $this->permission_protected ? 'true' : 'false',
                'permission_assigned' => $this->permission_assigned ? 'true' : 'false',
            ];

            if ($this->isEditing) {
                $this->groupPermission->update($data);
                session()->flash('success', 'Group Permission updated successfully.');
                return redirect()->route('group-permissions.edit', $this->groupPermission->group_permission_uuid);
            } else {
                $data['group_permission_uuid'] = (string) Str::uuid();
                
                $groupPermission = GroupPermission::create($data);
                session()->flash('success', 'Group Permission created successfully.');
                return redirect()->route('group-permissions.edit', $groupPermission->group_permission_uuid);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error saving Group Permission: ' . $e->getMessage());
        }
    }

    public function delete()
    {
        if (!$this->isEditing) {
            return;
        }

        try {
            $this->groupPermission->delete();
            session()->flash('success', 'Group Permission deleted successfully.');
            return redirect()->route('permissions.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting Group Permission: ' . $e->getMessage());
        }
    }

    public function toggleProtected()
    {
        $this->permission_protected = !$this->permission_protected;
    }

    public function toggleAssigned()
    {
        $this->permission_assigned = !$this->permission_assigned;
    }

    public function render()
    {
        return view('livewire.group-permission-form');
    }
}