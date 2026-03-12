<?php

use App\Models\ConferenceRoom;
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
        $tableName = ConferenceRoom::getTableName();
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
        $tableName = ConferenceRoom::getTableName();
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

        $table->uuid('conference_room_uuid')->nullable(false)->primary()->first();
        $table->uuid('conference_center_uuid')->nullable(false);
        $table->uuid('domain_uuid')->nullable(false);
        $table->string('conference_room_name', length: 255)->nullable(false);
        $table->string('profile', length: 255)->nullable(false);
        $table->enum('record', ['true','false'])->default('false')->nullable(false);
        $table->string('moderator_pin', length: 255)->nullable();
        $table->string('participant_pin', length: 255)->nullable();
        $table->unsignedTinyInteger('max_members')->nullable();
        $table->date('start_datetime')->nullable();
        $table->date('stop_datetime')->nullable();
        $table->enum('wait_mod', ['true','false'])->default('false')->nullable(false);
        $table->enum('moderator_endconf', ['true','false'])->default('false')->nullable(false);
        $table->enum('announce_name', ['true','false'])->default('false')->nullable(false);
        $table->enum('announce_count', ['true','false'])->default('false')->nullable(false);
        $table->enum('announce_recording', ['true','false'])->default('false')->nullable(false);
        $table->enum('mute', ['true','false'])->default('false')->nullable(false);
        $table->string('email_address', length: 255)->nullable();
        $table->string('account_code', length: 255)->nullable();
        $table->longText('description')->nullable();
        $table->enum('enabled', ['true','false'])->default('false')->nullable(false);

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX conference center profile information');

        $table->foreign('domain_uuid')->on(Domain::getTableName())->references('domain_uuid')->cascadeOnDelete()->cascadeOnUpdate();
    }
};
