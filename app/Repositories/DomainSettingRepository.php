<?php

namespace App\Repositories;

use App\Models\DomainSetting;
use App\Models\Domain;
use App\Services\DomainSettingService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DomainSettingRepository
{
    protected DomainSettingService $domainSettingService;

    public function __construct(DomainSettingService $domainSettingService)
    {
        $this->domainSettingService = $domainSettingService;
    }
    public function getAllByDomain(string $domainUuid, bool $onlyEnabled = false): Collection
    {
        $query = DomainSetting::where('domain_uuid', $domainUuid)
            ->orderBy('domain_setting_category')
            ->orderBy('domain_setting_subcategory')
            ->orderBy('domain_setting_order');

        if ($onlyEnabled) {
            $query->where('domain_setting_enabled', 'true');
        }

        return $query->get();
    }

    public function findByCategoryAndSubcategory(
        string $category,
        string $subcategory,
        string $domainUuid,
        bool $onlyEnabled = true
    ): Collection {
        $query = DomainSetting::where('domain_setting_category', $category)
            ->where('domain_setting_subcategory', $subcategory)
            ->where('domain_uuid', $domainUuid)
            ->orderBy('domain_setting_order');

        if ($onlyEnabled) {
            $query->where('domain_setting_enabled', 'true');
        }

        return $query->get();
    }

    public function getValue(
        string $domainUuid,
        string $category,
        string $subcategory,
        string $name
    ): ?string {
        $setting = DomainSetting::where('domain_uuid', $domainUuid)
            ->where('domain_setting_category', $category)
            ->where('domain_setting_subcategory', $subcategory)
            ->where('domain_setting_name', $name)
            ->where('domain_setting_enabled', 'true')
            ->first();

        return $setting?->domain_setting_value;
    }


    public function getArrayValues(
        string $domainUuid,
        string $category,
        string $subcategory
    ): Collection {
        return DomainSetting::where('domain_uuid', $domainUuid)
            ->where('domain_setting_category', $category)
            ->where('domain_setting_subcategory', $subcategory)
            ->where('domain_setting_enabled', 'true')
            ->orderBy('domain_setting_order')
            ->get()
            ->pluck('domain_setting_value');
    }


    public function create(array $data): DomainSetting
    {
        DB::beginTransaction();
        try {
            $data['domain_setting_uuid'] = $data['domain_setting_uuid'] ?? Str::uuid()->toString();
            $data['domain_setting_enabled'] = $data['domain_setting_enabled'] ?? 'true';
            $data['domain_setting_order'] = $data['domain_setting_order'] ?? 0;

            $data['app_uuid'] = config('coolpbx.domain_settings.app_uuid');

            $data['domain_setting_category'] = strtolower($data['domain_setting_category']);
            $data['domain_setting_subcategory'] = strtolower($data['domain_setting_subcategory']);
            $data['domain_setting_name'] = strtolower($data['domain_setting_name']);

            $setting = DomainSetting::create($data);

            $this->domainSettingService->processAfterSave($setting);

            DB::commit();
            return $setting;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    public function update(string $domainSettingUuid, array $data): bool
    {
        DB::beginTransaction();
        try {
            if (isset($data['domain_setting_category'])) {
                $data['domain_setting_category'] = strtolower($data['domain_setting_category']);
            }
            if (isset($data['domain_setting_subcategory'])) {
                $data['domain_setting_subcategory'] = strtolower($data['domain_setting_subcategory']);
            }
            if (isset($data['domain_setting_name'])) {
                $data['domain_setting_name'] = strtolower($data['domain_setting_name']);
            }

            $setting = DomainSetting::where('domain_setting_uuid', $domainSettingUuid)->firstOrFail();
            $updated = $setting->update($data);

            if ($updated) {
                $setting->refresh();
                
                $this->domainSettingService->processAfterSave($setting);
            }

            DB::commit();
            return $updated;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    public function delete(string $domainSettingUuid): bool
    {
        $setting = DomainSetting::findOrFail($domainSettingUuid);
        return $setting->delete();
    }


    public function findByUuid(string $domainSettingUuid): ?DomainSetting
    {
        return DomainSetting::find($domainSettingUuid);
    }

    public function exists(
        string $domainUuid,
        string $category,
        string $subcategory,
        string $name
    ): bool {
        return DomainSetting::where('domain_uuid', $domainUuid)
            ->where('domain_setting_category', $category)
            ->where('domain_setting_subcategory', $subcategory)
            ->where('domain_setting_name', $name)
            ->exists();
    }

    public function getGroupedByCategory(string $domainUuid, bool $onlyEnabled = true): Collection
    {
        $query = DomainSetting::where('domain_uuid', $domainUuid)
            ->orderBy('domain_setting_category')
            ->orderBy('domain_setting_subcategory')
            ->orderBy('domain_setting_order');

        if ($onlyEnabled) {
            $query->where('domain_setting_enabled', 'true');
        }

        return $query->get()->groupBy('domain_setting_category');
    }

    public function getByCategory(string $domainUuid, string $category, bool $onlyEnabled = true): Collection
    {
        $query = DomainSetting::where('domain_uuid', $domainUuid)
            ->where('domain_setting_category', $category)
            ->orderBy('domain_setting_subcategory')
            ->orderBy('domain_setting_order');

        if ($onlyEnabled) {
            $query->where('domain_setting_enabled', 'true');
        }

        return $query->get();
    }

    public function bulkUpdate(array $settings): bool
    {
        DB::beginTransaction();
        try {
            foreach ($settings as $uuid => $data) {
                $this->update($uuid, $data);
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getNextOrder(string $domainUuid, string $category, string $subcategory): int
    {
        $maxOrder = DomainSetting::where('domain_uuid', $domainUuid)
            ->where('domain_setting_category', $category)
            ->where('domain_setting_subcategory', $subcategory)
            ->max('domain_setting_order');

        return ($maxOrder ?? 0) + 10;
    }

    public function cloneToDomain(string $sourceDomainUuid, string $targetDomainUuid): int
    {
        $sourceSettings = $this->getAllByDomain($sourceDomainUuid);
        $clonedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($sourceSettings as $setting) {
                $newSetting = $setting->replicate();
                $newSetting->domain_setting_uuid = Str::uuid()->toString();
                $newSetting->domain_uuid = $targetDomainUuid;
                $newSetting->save();
                $clonedCount++;
            }
            DB::commit();
            return $clonedCount;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getAllowedCategories(): ?array
    {
        if (auth()->user()->hasPermission('domain_setting_category_edit')) {
            return null;
        }

        $categories = [];
        $userGroups = session('groups', []);
        $settings = session('settings', []);

        foreach ($userGroups as $group) {
            $groupName = $group['group_name'];
            if (isset($settings[$groupName])) {
                foreach ($settings[$groupName] as $category) {
                    $categories[] = strtolower($category);
                }
            }
        }

        return !empty($categories) ? array_unique($categories) : null;
    }

    public function canEditCategory(string $category): bool
    {
        $allowedCategories = $this->getAllowedCategories();

        // Si es null, tiene acceso a todas
        if ($allowedCategories === null) {
            return true;
        }

        return in_array(strtolower($category), $allowedCategories);
    }
}
