<?php

use App\Models\XmlCDR;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Staudenmeir\LaravelMigrationViews\Facades\Schema as ViewSchema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!ViewSchema::hasView('view_call_recordings')) {
            $query = XmlCDR::select('domain_uuid', 'xml_cdr_uuid as call_recording_uuid', 'caller_id_name', 'caller_id_number', 'caller_destination', 'destination_number',                'record_name as call_recording_name', 'record_path as call_recording_path', 'duration as call_recording_length', 'start_stamp as call_recording_date', 'direction as call_direction')
                ->whereNotNull('record_name')
                ->whereNotNull('record_path')
                ->orderBy('start_stamp','desc');
            ViewSchema::createView('view_call_recordings', $query, algorithm: 'TEMPTABLE');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        ViewSchema::dropViewIfExists('view_call_recordings');
    }
};
