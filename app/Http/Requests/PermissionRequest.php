<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermissionRequest extends FormRequest
{
    protected $isEditing = false;
    protected $permissionUuid = null;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function setEditingContext(bool $isEditing, ?string $permissionUuid = null): self
    {
        $this->isEditing = $isEditing;
        $this->permissionUuid = $permissionUuid;
        return $this;
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'application_name' => 'required|string|max:255',
            'permission_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('v_permissions', 'permission_name')->ignore($this->isEditing ? $this->permissionUuid : null, 'permission_uuid'),
            ],
            'permission_description' => 'nullable|string|max:500',
            'selectedGroups' => 'array',
            'selectedGroups.*' => 'exists:v_groups,group_uuid',
        ];
    }
}
