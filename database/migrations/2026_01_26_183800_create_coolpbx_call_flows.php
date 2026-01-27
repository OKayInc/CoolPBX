<?php

use App\Models\CallFlow;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableName = CallFlow::getTableName();
        if (!Schema::hasTable($tableName))
        {
            Schema::create($tableName, function (Blueprint $table)
            {
                $this->dql($table);
            });
        }
        else
        {
            Schema::table($tableName, function (Blueprint $table) {
                $this->dql($table);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = CallFlow::getTableName();
        Schema::dropIfExists($tableName);
    }

    private function dql(Blueprint $table): void
    {
        switch (env('DB_CONNECTION', 'mysql'))
        {
            case 'mariadb':
            case 'mysql':
                $table->collation('unicode_ci');
                $table->engine('InnoDB');
                break;
            case 'pgsql':
                $table->collation('en_US.utf8');
        }

        $table->uuid('call_flow_uuid')->nullable(false)->primary()->first();
        $table->uuid('domain_uuid')->nullable(false);
        $table->uuid('dialplan_uuid')->nullable(false);
        $table->string('call_flow_name', length: 255)->nullable(false);
        $table->string('call_flow_extension', length: 255)->nullable(false);
        $table->string('call_flow_feature_code', length: 255)->nullable(false);
        $table->string('call_flow_context', length: 255)->nullable(false);
        $table->enum('call_flow_status', ['true','false'])->default('false')->nullable(false);
        $table->string('call_flow_pin_number', length: 255)->nullable();
        $table->string('call_flow_label', length: 255)->nullable();
        $table->string('call_flow_sound', length: 255)->nullable();
        $table->string('call_flow_app', length: 255)->nullable(false);
        $table->string('call_flow_data', length: 255)->nullable(false);
        $table->string('call_flow_alternate_label', length: 255)->nullable();
        $table->string('call_flow_alternate_sound', length: 255)->nullable();
        $table->string('call_flow_alternate_app', length: 255)->nullable();
        $table->string('call_flow_alternate_data', length: 255)->nullable();
        $table->enum('call_flow_enabled', ['true','false'])->default('false')->nullable(false);
        $table->longText('call_flow_description')->nullable();

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX call flow information');
    }
};
