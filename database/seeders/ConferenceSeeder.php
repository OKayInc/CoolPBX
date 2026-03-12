<?php

namespace Database\Seeders;

use App\Models\DialplanDetail as Model;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CallBlockSeeder extends Seeder
{
    use WithoutModelEvents;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sql = "UPDATE ".Model::getTableName()." SET dialplan_detail_data = REPLACE(dialplan_detail_data, '-','@') WHERE dialplan_detail_type = 'conference' AND dialplan_detail_data LIKE '%-%';";
        DB:statement($sql);
    }
}
