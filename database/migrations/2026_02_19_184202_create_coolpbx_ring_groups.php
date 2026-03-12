<?php

use App\Models\Domain;
use App\Models\RingGroup;
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
        $tableName = RingGroup::getTableName();
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
        $tableName = RingGroup::getTableName();
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

        $table->uuid('ring_group_uuid')->nullable(false)->primary()->first();
        $table->uuid('domain_uuid')->nullable(false);
        $table->string('ring_group_name', length: 255)->nullable(false);
        $table->string('ring_group_extension', length: 255)->nullable(false);
        $table->string('ring_group_greeting', length: 255)->nullable();
        $table->string('ring_group_context', length: 255)->nullable(false);
        $table->unsignedTinyInteger('ring_group_call_timeout')->default(30)->nullable(false);
        $table->string('ring_group_forward_destination', length: 255)->nullable(false);
        $table->enum('ring_group_forward_enabled', ['true','false'])->default('false')->nullable(false);
        $table->string('ring_group_caller_id_name', length: 255)->nullable(false);
        $table->string('ring_group_caller_id_number', length: 255)->nullable(false);
        $table->string('ring_group_cid_name_prefix', length: 255)->nullable(false);
        $table->string('ring_group_cid_number_prefix', length: 255)->nullable(false);
        $table->enum('ring_group_strategy', ['simultaneous', 'sequence', 'enterprise', 'rollover', 'random'])->default('simultaneous')->nullable(false);
        $table->string('ring_group_timeout_app', length: 255)->nullable();
        $table->string('ring_group_timeout_data', length: 255)->nullable();
        $table->string('ring_group_distinctive_ring', length: 255)->nullable();
        $table->string('ring_group_ringback', length: 255)->nullable();
        $table->enum('ring_group_call_forward_enabled', ['true','false'])->default('false')->nullable(false);
        $table->enum('ring_group_follow_me_enabled', ['true','false'])->default('false')->nullable(false);
        $table->string('ring_group_missed_call_app', length: 255)->nullable();
        $table->string('ring_group_missed_call_data', length: 255)->nullable();
        $table->longText('ring_group_description')->nullable();
        $table->enum('ring_group_enabled', ['true','false'])->default('false')->nullable(false);

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX ring group information');

        $table->foreign('domain_uuid')->on(Domain::getTableName())->references('domain_uuid')->cascadeOnDelete()->cascadeOnUpdate();
    }
};
