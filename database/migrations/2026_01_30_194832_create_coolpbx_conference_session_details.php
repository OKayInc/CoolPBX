<?php

use App\Models\ConferenceSessionDetail;
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
        $tableName = ConferenceSessionDetail::getTableName();
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
        $tableName = ConferenceSessionDetail::getTableName();
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

        $table->uuid('conference_session_detail_uuid')->nullable(false)->primary()->first();
        $table->uuid('domain_uuid')->nullable(false);
        $table->uuid('conference_session_uuid')->nullable(false);
        $table->uuid('meeting_uuid')->nullable(false);
        $table->string('username', length: 255)->nullable(false);
        $table->string('caller_id_name', length: 255)->nullable(false);
        $table->string('caller_id_number', length: 255)->nullable(false);
        $table->uuid('uuid')->nullable(false);
        $table->string('moderator', length: 255)->nullable();
        $table->ipAddress('network_addr')->nullable(false);
        $table->unsignedBigInteger('start_epoch')->nullable(false);
        $table->unsignedBigInteger('end_epoch')->nullable(false);

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX conference center session detail information');

        $table->foreign('domain_uuid')->on(Domain::getTableName())->references('domain_uuid')->cascadeOnDelete()->cascadeOnUpdate();
    }
};
