<?php

namespace Database\Seeders;

use App\Models\CallBlock as Model;
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
        foreach (Model::all() as $m)
        {
            if (empty($m->call_block_direction)){
                $m->call_block_direction = 'inbound';
                $m->save();
            }
        }
    }
}
