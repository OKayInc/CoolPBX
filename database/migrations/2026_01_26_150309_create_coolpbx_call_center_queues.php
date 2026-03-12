<?php

use App\Models\CallCenterQueue;
use App\Models\Domain;
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
        $tableName = CallCenterQueue::getTableName();
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
        $tableName = CallCenterQueue::getTableName();
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

        $table->uuid('call_center_queue_uuid')->nullable(false)->primary()->first();
        $table->uuid('domain_uuid')->nullable(false);
        $table->uuid('dialplan_uuid')->nullable(false);
        $table->string('queue_name', length: 255)->nullable(false);
        $table->string('queue_extension', length: 255)->nullable(false);
        $table->string('queue_greeting', length: 255)->nullable(false);
        $table->enum('queue_strategy', ['longest-idle-agent','round-robin','top-down','agent-with-least-talk-time','agent-with-fewest-calls','sequentially-by-agent-order','random','ring-all'])->default('longest-idle-agent')->nullable(false);
        $table->string('queue_moh_sound', length: 255)->nullable(false);
        $table->enum('queue_record_template', ['true','false'])->default('false')->nullable(false);
        $table->enum('queue_time_base_score', ['system','queue'])->default('system')->nullable(false);
        $talbe->unsignedInteger('queue_time_base_score_sec')->nullable();
        $talbe->unsignedInteger('queue_max_wait_time')->nullable();
        $talbe->unsignedInteger('queue_max_wait_time_with_no_agent')->nullable();
        $talbe->unsignedInteger('queue_max_wait_time_with_no_agent_time_reached')->nullable();
        $table->enum('queue_tier_rules_apply', ['true','false'])->default('false')->nullable(false);
        $talbe->unsignedInteger('queue_tier_rule_wait_second')->nullable();
        $table->enum('queue_tier_rule_no_agent_no_wait', ['true','false'])->default('false')->nullable(false);
        $table->string('queue_timeout_action', length: 255)->nullable(false);
        $talbe->unsignedInteger('queue_discard_abandoned_after')->nullable();
        $table->enum('queue_abandoned_resume_allowed', ['true','false'])->default('false')->nullable(false);
        $table->enum('queue_tier_rule_wait_multiply_level', ['true','false'])->default('false')->nullable(false);
        $table->string('queue_cid_prefix', length: 255)->nullable();
        $table->string('queue_outbound_caller_id_name', length: 255)->nullable();
        $table->string('queue_outbound_caller_id_number', length: 255)->nullable();
        $table->enum('queue_announce_position', ['true','false'])->default('false')->nullable(false);
        $table->string('queue_announce_sound', length: 255)->nullable(false);
        $talbe->unsignedInteger('queue_announce_frequency')->default(0)->nullable(false);
        $table->string('queue_cc_exit_keys', length: 255)->nullable(false);
        $table->string('queue_email_address', length: 255)->nullable(false);
        $table->longText('queue_description')->nullable();

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX call center queue information');

        $table->foreign('domain_uuid')->on(Domain::getTableName())->references('domain_uuid')->cascadeOnDelete()->cascadeOnUpdate();
    }
};
