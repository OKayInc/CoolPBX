<?php

use App\Models\CallCenterAgent;
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
        $tableName = CallCenterAgent::getTableName();
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
        $tableName = CallCenterAgent::getTableName();
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

        $table->uuid('call_center_agent_uuid')->nullable(false)->primary()->first();
        $table->uuid('domain_uuid')->nullable(false);
        $table->uuid('user_uuid')->nullable();
        $table->string('agent_name', length: 255)->nullable(false);
        $table->enum('agent_type', ['callback','uuid-standby'])->default('callback')->nullable(false);
        $talbe->unsignedTinyInteger('agent_call_timeout')->nullable();
        $talbe->unsignedTinyInteger('agent_id')->nullable();
        $table->string('agent_password', length: 255)->nullable(false);
        $table->string('agent_contact', length: 255)->nullable(false);
        $table->enum('agent_status', ['Logged Out','Available','Available (On Demand)','On Break'])->default('Available')->nullable(false);
        $talbe->unsignedInteger('agent_max_no_answer')->nullable();
        $talbe->unsignedInteger('agent_wrap_up_time')->nullable();
        $talbe->unsignedInteger('agent_reject_delay_time')->nullable();
        $talbe->unsignedInteger('agent_busy_delay_time')->nullable();
        $talbe->unsignedInteger('agent_no_answer_delay_time')->nullable();
        $table->enum('agent_record', ['true','false'])->default('false')->nullable(false);

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX call center agent information');
    }
};
