<?php

namespace Database\Seeders;

use App\Models\ConferenceProfile;
use App\Models\ConferenceProfileDetail;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConferenceProfileSeeder extends Seeder
{
    use WithoutModelEvents;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $conferenceProfileCount = ConferenceProfile::all()->count();

        if ($conferenceProfileCount == 0)
        {
            $profiles = config('coolpbx.conference_profiles.profiles');

            foreach ($profiles as $profile)
            {
                $newProfile = ConferenceProfile::create($profile);
                foreach ($profile['params'] as $profileParams)
                {
                    $profileParams['conference_profile_uuid'] = $newProfile->conference_profile_uuid;
                    $newParam = ConferenceProfileDetail::create($profileParams);
                }
            }
        }
    }
}
