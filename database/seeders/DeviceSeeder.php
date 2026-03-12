<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\DeviceKey;
use App\Models\DeviceLine;
use App\Models\DeviceProfileKey;
use App\Models\DeviceProfileSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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

        }
    }
}
