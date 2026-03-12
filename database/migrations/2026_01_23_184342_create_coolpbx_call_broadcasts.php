<?php

use App\Models\CallBroadcast;
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
        $tableName = CallBroadcast::getTableName();
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
        $tableName = CallBroadcast::getTableName();
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

        $table->uuid('call_broadcast_uuid')->nullable(false)->primary()->first();
        $table->uuid('domain_uuid')->nullable(false);
        $table->string('broadcast_name', length: 255)->nullable(false);
        $talbe->unsignedBigInteger('broadcast_start_time')->nullable();
        $talbe->unsignedInteger('broadcast_timeout')->nullable();
        $talbe->unsignedInteger('broadcast_concurrent_limit')->nullable();
        $table->uuid('recording_uuid')->nullable();
        $table->string('broadcast_caller_id_name', length: 255)->nullable();
        $table->string('broadcast_caller_id_number', length: 255)->nullable();
        $table->string('broadcast_destination_type', length: 255)->nullable();
        $table->longText('broadcast_phone_numbers')->nullable();
        $table->enum('broadcast_avmd', ['true','false'])->default('false')->nullable(false);
        $table->string('broadcast_destination_data', length: 255)->nullable();
        $table->string('broadcast_accountcode', length: 255)->nullable();
        $table->string('broadcast_toll_allow', length: 255)->nullable();
        $table->longText('broadcast_description')->nullable();

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX call broadcast information');

        $table->foreign('domain_uuid')->on(Domain::getTableName())->references('domain_uuid')->cascadeOnDelete()->cascadeOnUpdate();
    }
};
