<?php

namespace App\Repositories;

use App\Models\MusicOnHold;
use App\Models\Variable;

class RingbacksRepository {
    
    public function getRingtones()
    {
        return Variable::where('var_category', 'ringbacks')
                ->orderBy('var_name')->get();
    }

    public function getMusicOnHold()
    {
        return MusicOnHold::where('var_category', 'moh')
                ->orderBy('var_name')->get();
    }


}