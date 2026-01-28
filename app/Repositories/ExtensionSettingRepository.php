<?php

namespace App\Repositories;

use App\Models\Extension;
use App\Models\ExtensionSetting;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ExtensionSettingRepository
{
    protected ExtensionSetting $extensionSetting;
    protected Extension $extension;

    public function __construct(
        ExtensionSetting $extensionSetting,
        Extension $extension
    ) {
        $this->extensionSetting = $extensionSetting;
        $this->extension = $extension;
    }


    public function findByUuid(string $extensionSettingUuid, bool $withRelations = false): ?ExtensionSetting
    {
        $query = $this->extensionSetting->where('extension_setting_uuid', $extensionSettingUuid);

        if ($withRelations) {
            $query->with(['extension', 'domain']);
        }

        return $query->first();
    }

    public function findByExtensionUuid(string $extensionUuid): Collection
    {
        return $this->extensionSetting
            ->where('extension_uuid', $extensionUuid)
            ->orderBy('extension_setting_type', 'asc')
            ->orderBy('extension_setting_name', 'asc')
            ->get();
    }

    public function checkDuplicateSetting(
        string $extensionUuid,
        string $settingType,
        string $settingName,
        ?string $excludeextensionSettingUuid = null
    ): bool {
        $query = $this->extensionSetting
            ->where('extension_uuid', $extensionUuid)
            ->where('extension_setting_type', $settingType)
            ->where('extension_setting_name', $settingName);

        if ($excludeextensionSettingUuid) {
            $query->where('extension_setting_uuid', '!=', $excludeextensionSettingUuid);
        }

        return $query->exists();
    }


    public function getTotalSettingsCount(string $extensionUuid): int
    {
        return $this->extensionSetting->where('extension_uuid', $extensionUuid)->count();
    }

    public function create(array $settingData): ExtensionSetting
    {
        $settingData['extension_setting_uuid'] = $settingData['extension_setting_uuid'] ?? Str::uuid();

        try {
            DB::beginTransaction();

            $this->setDefaultValues($settingData);

            $this->validateSettingData($settingData);

            $filteredData = $this->applySettingPermissions($settingData);

            $setting = $this->extensionSetting->create($filteredData);



            DB::commit();
            return $setting;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(string $extensionSettingUuid, array $settingData): ExtensionSetting
    {
        try {
            DB::beginTransaction();

            $setting = $this->findByUuid($extensionSettingUuid);
            if (!$setting) {
                throw new Exception("Extension setting not found");
            }

            $this->validateSettingData($settingData, $setting);

            $filteredData = $this->applySettingPermissions($settingData, $setting);

            $setting->update($filteredData);


            DB::commit();
            return $setting->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(string $extensionSettingUuid): void
    {
        try {
            DB::beginTransaction();

            $setting = $this->findByUuid($extensionSettingUuid);
            if (!$setting) {
                throw new Exception("Extension setting not found");
            }

            $extensionUuid = $setting->extension_uuid;
            $setting->delete();


            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function toggle(string $extensionSettingUuid): ExtensionSetting
    {
        try {
            DB::beginTransaction();

            $setting = $this->findByUuid($extensionSettingUuid);
            if (!$setting) {
                throw new Exception("Extension setting not found");
            }

            $newStatus = !$setting->extension_setting_enabled;
            $setting->update(['extension_setting_enabled' => $newStatus]);


            DB::commit();
            return $setting->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    public function copy(string $extensionSettingUuid): ExtensionSetting
    {
        try {
            DB::beginTransaction();

            $originalSetting = $this->findByUuid($extensionSettingUuid);
            if (!$originalSetting) {
                throw new Exception("Extension setting not found");
            }

            $newSettingData = [
                'extension_setting_uuid' => Str::uuid(),
                'extension_uuid' => $originalSetting->extension_uuid,
                'domain_uuid' => $originalSetting->domain_uuid,
                'extension_setting_type' => $originalSetting->extension_setting_type,
                'extension_setting_name' => $originalSetting->extension_setting_name . ' (copy)',
                'extension_setting_value' => $originalSetting->extension_setting_value,
                'extension_setting_enabled' => $originalSetting->extension_setting_enabled,
                'extension_setting_description' => $originalSetting->extension_setting_description,
            ];

            $newSetting = $this->extensionSetting->create($newSettingData);


            DB::commit();
            return $newSetting;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getAvailableTypes(): array
    {
        return [
            'param' => 'Parameter',
            'variable' => 'Variable'
        ];
    }

    public function setDefaultValues(array &$settingData): void
    {
        $defaults = [
            'extension_setting_enabled' => true,
            'domain_uuid' => auth()->user()->domain_uuid ?? session('domain_uuid'),
        ];

        foreach ($defaults as $key => $value) {
            if (empty($settingData[$key])) {
                $settingData[$key] = $value;
            }
        }
    }

    private function validateSettingData(array $settingData, ?ExtensionSetting $existingSetting = null): void
    {
        $errors = [];

        if (empty($settingData['extension_setting_type']) && (!$existingSetting || empty($existingSetting->extension_setting_type))) {
            $errors[] = 'Extension setting type is required';
        }

        if (empty($settingData['extension_setting_name']) && (!$existingSetting || empty($existingSetting->extension_setting_name))) {
            $errors[] = 'Extension setting name is required';
        }

        if (!isset($settingData['extension_setting_enabled']) && (!$existingSetting || !isset($existingSetting->extension_setting_enabled))) {
            $errors[] = 'Extension setting enabled status is required';
        }

        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }
    }

    private function applySettingPermissions(array $settingData, ?ExtensionSetting $existingSetting = null): array
    {
        $filteredData = [];
        $user = auth()->user();

        $filteredData['domain_uuid'] = $settingData['domain_uuid'] ?? ($existingSetting->domain_uuid ?? $user->domain_uuid);

        if (is_null($existingSetting)) {
            $filteredData['extension_setting_uuid'] = $settingData['extension_setting_uuid'] ?? Str::uuid();
            $filteredData['extension_uuid'] = $settingData['extension_uuid'];
        }

        if ($user->hasPermission('extension_setting_add') || $user->hasPermission('extension_setting_edit')) {
            $filteredData['extension_setting_type'] = $settingData['extension_setting_type'] ?? ($existingSetting->extension_setting_type ?? null);
            $filteredData['extension_setting_name'] = $settingData['extension_setting_name'] ?? ($existingSetting->extension_setting_name ?? null);
            $filteredData['extension_setting_value'] = $settingData['extension_setting_value'] ?? ($existingSetting->extension_setting_value ?? null);
            $filteredData['extension_setting_enabled'] = $settingData['extension_setting_enabled'] ?? ($existingSetting->extension_setting_enabled ?? true);
            $filteredData['extension_setting_description'] = $settingData['extension_setting_description'] ?? ($existingSetting->extension_setting_description ?? null);
        }

        return array_filter($filteredData, function ($value) {
            return !is_null($value);
        });
    }

    public function bulkDelete(array $extensionSettingUuids): int
    {
        try {
            DB::beginTransaction();

            $settings = $this->extensionSetting->whereIn('extension_setting_uuid', $extensionSettingUuids)->get();
            $extensionUuids = $settings->pluck('extension_uuid')->unique();
            
            $count = $this->extensionSetting->whereIn('extension_setting_uuid', $extensionSettingUuids)->delete();

            DB::commit();
            return $count;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function bulkToggle(array $extensionSettingUuids): int
    {
        try {
            DB::beginTransaction();

            $settings = $this->extensionSetting->whereIn('extension_setting_uuid', $extensionSettingUuids)->get();
            $extensionUuids = $settings->pluck('extension_uuid')->unique();
            
            $count = 0;
            foreach ($settings as $setting) {
                $newStatus = !$setting->extension_setting_enabled;
                $setting->update(['extension_setting_enabled' => $newStatus]);
                $count++;
            }

            DB::commit();
            return $count;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}