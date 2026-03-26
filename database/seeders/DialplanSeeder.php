<?php

namespace Database\Seeders;

use App\Models\Dialplan;
use App\Models\Domain;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DialplanSeeder extends Seeder
{
    use WithoutModelEvents;

    protected $dialplanRepository;
	protected $dialplanDetailRepository;

	public function __construct(DialplanRepository $dialplanRepository, DialplanDetailRepository $dialplanDetailRepository)
    {
        $this->dialplanRepository = $dialplanRepository;
        $this->dialplanDetailRepository = $dialplanDetailRepository;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $repo = Storage::disk('templates');
        $files = $repo->allFiles('dialplans/');
        foreach ($files as $file)
        {
            $extension = Str::lower(pathinfo($file, PATHINFO_EXTENSION));
            if ($extension == 'xml')
            {
                if ($xml = $repo::get($file))
                {
                    if (!$this->dialplanRepository->import($xml))
                    {
                        // Fail, handle the error
                    }
                }
            }
        }

        $sql = "UPDATE ".Dialplan::getTableName()." SET dialplan_order = '870' WHERE dialplan_order = '980' AND dialplan_name = 'cidlookup';";
        DB:statement($sql);
        unset($sql);

        $sql = "UPDATE ".Dialplan::getTableName()." SET dialplan_order = '880' WHERE dialplan_order = '990' AND dialplan_name = 'call_screen';";
        DB:statement($sql);
        unset($sql);

        $sql = "UPDATE ".Dialplan::getTableName()." SET dialplan_order = '890' WHERE dialplan_order = '999' AND dialplan_name = 'local_extension';";
        DB:statement($sql);
        unset($sql);
    }
}
