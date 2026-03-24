<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\DeviceKey;
use App\Models\DeviceLine;
use App\Models\DeviceProfileKey;
use App\Models\DeviceProfileSetting;
use App\Models\DeviceVendor;
use App\Models\DeviceVendorFunction;
use App\Models\DeviceVendorFunctionGroup;
use App\Models\Group;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class DeviceSeeder extends Seeder
{
    use WithoutModelEvents;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sql = "UPDATE ".DeviceLine::getTableName()." SET enabled = 'true' WHERE enabled IS NULL OR enabled = '';";
        DB:statement($sql);
        unset($sql);

        $sql = "UPDATE ".DeviceLine::getTableName()." SET label = user_id WHERE label IS NULL;";
        DB:statement($sql);
        unset($sql);

        $deviceKeys = DeviceKey::join(Device::getTableName(), DeviceKey::getTableName().'.device_uuid', '=', Device::getTableName().'.device_uuid')
            ->whereNotNull(DeviceKey::getTableName().'.device_uuid')
            ->whereNull(DeviceKey::getTableName().'.device_key_vendor')
            ->get();

        foreach ($deviceKeys as $deviceKey)
        {
            if (!is_null($currentKey = DeviceKey::find($deviceKey->device_key_uuid)))
            {
                $currentKey->device_key_vendor = $deviceKey->device_vendor;
                $currentKey->save();
            }
        }

        $deviceProfileKeysCount = DeviceProfileKey::all()->count();
        if ($deviceProfileKeysCount == 0)
        {
            $deviceKeys = DeviceKey::whereNotNull('device_profile_uuid');

            foreach ($deviceKeys as $deviceKey)
            {
                $data = [
                    'device_profile_key_uuid' => $deviceKey->device_key_uuid,
                    'domain_uuid' => $deviceKey->domain_uuid,
                    'device_profile_uuid' => $deviceKey->device_profile_uuid,
                    'profile_key_id' => $deviceKey->device_key_id,
                    'profile_key_category' =>$deviceKey->device_key_category,
                    'profile_key_vendor' => $deviceKey->device_key_vendor,
                    'profile_key_type' => $deviceKey->device_key_type,
                    'profile_key_line' => $deviceKey->device_key_line,
                    'profile_key_value' => $deviceKey->device_key_value,
                    'profile_key_extension' => $deviceKey->device_key_extension,
                    'profile_key_protected' => $deviceKey->device_key_protected,
                    'profile_key_label' => $deviceKey->profile_key_label,
                    'profile_key_icon' => $deviceKey->device_key_icon,
                ];
                DeviceProfileKey::create($data);
                unset($data);
            }
        }

        $deviceProfileSettingsCount = DeviceProfileSetting::all()->count();
        if ($deviceProfileSettingsCount == 0)
        {
            $deviceSettings = DeviceSetting::whereNotNull('device_profile_uuid');

            foreach ($deviceSettings as $deviceSetting)
            {
                $data = [
                    'device_profile_setting_uuid' => $deviceKey->device_setting_uuid,
                    'domain_uuid' => $deviceKey->domain_uuid,
                    'device_profile_uuid' => $deviceKey->device_profile_uuid,
                    'profile_setting_name' => $deviceKey->device_setting_subcategory,
                    'profile_setting_value' =>$deviceKey->device_setting_value,
                    'profile_setting_enabled' => $deviceKey->device_setting_enabled,
                    'profile_setting_description' => $deviceKey->device_setting_description,
                ];
                DeviceProfileSetting::create($data);
                unset($data);
            }
        }

        $deviceVendorCount = DeviceVendor::all()->count();
        if ($deviceVendorCount == 0)
        {
            //$deviceSettings = DeviceSetting::whereNotNull('device_profile_uuid');
            $response = Http::get(config('coolpbx.devices.vendors_template'));
            if ($response->ok())
            {
                $deviceVendors = yaml_parse($response->body());
                if ($deviceVendors !== false)
                    foreach ($deviceVendors as $deviceVendor)
                    {
                        $data = [
                            'device_vendor_uuid' => $deviceVendor['uuid'],
                            'name' => $deviceVendor['name'],
                            'enabled' => 'true',
                            'description' => (array_key_exists('description', $deviceVendor) ? $deviceVendor['description'] : NULL),
                        ];
                        DeviceVendor::create($data);
                        DeviceVendorFunction::where('device_vendor_uuid', '=', $deviceVendor['uuid'])->delete();

                        foreach ($deviceVendor['functions'] as $deviceVendorFunction)
                        {
                            $data2 = [
                                'device_vendor_uuid' => $deviceVendor['uuid'],
                                'type' => $deviceVendorFunction['type'],
                                'subtype' => (strlen($deviceVendorFunction['subtype']) > 0) ? $deviceVendorFunction['subtype'] :  NULL,
                                'value' => $deviceVendorFunction['value'],
                                'enabled' => 'true',
                                'description' => (array_key_exists('description', $deviceVendor) ? $deviceVendor['description'] : NULL),
                            ];
                            $newDeviceVendorFunction = DeviceVendorFunction::create($data2);
                            DeviceVendorFunctionGroup::where('device_vendor_function_uuid', '=', $newDeviceVendorFunction->device_vendor_function_uuid)->delete();

                            foreach ($deviceVendorFunction['groups'] as $deviceVendorFunctionGroup)
                            {
                                $group = Group::where('group_name', '=', $deviceVendorFunctionGroup)
                                            ->whereNull('domain_uuid')
                                            ->get();
                                if ($group)
                                {
                                    $data3 = [
                                        'device_vendor_function_uuid' => $newDeviceVendorFunction->device_vendor_function_uuid,
                                        'device_vendor_uuid' => $deviceVendor['uuid'],
                                        'group_uuid' => $group->group_uuid,
                                        'group_name' => $group->group_name,     // TODO: make sure this is gone
                                    ];
                                    DeviceVendorFunctionGroup::create($data3);
                                    unset($data3);
                                }
                            }

                            unset($data2);
                        }

                        unset($data);
                    }
            }
        }
    }
}
