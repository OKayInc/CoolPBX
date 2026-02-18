<?php

use App\Models\MusicOnHold;
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
        $tableName = MusicOnHold::getTableName();
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
        $tableName = MusicOnHold::getTableName();
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

        $table->uuid('music_on_hold_uuid')->nullable(false)->primary()->first();
        $table->uuid('domain_uuid')->nullable();
        $table->string('music_on_hold_name', length: 255)->nullable(false);
        $table->string('music_on_hold_path', length: 255)->nullable();
        $table->enum('music_on_hold_rate', [8000, 16000, 32000, 48000])->nullable(false);
        $table->enum('music_on_hold_shuffle', ['true','false'])->default('false')->nullable(false);
        $table->unsignedTinyInteger('music_on_hold_channels')->nullable(false);
        $table->unsignedTinyInteger('music_on_hold_interval')->nullable();
        $table->string('music_on_hold_timer_name', length: 255)->nullable();
        $table->string('music_on_hold_chime_list', length: 1024)->nullable();
        $table->enum('music_on_hold_chime_freq', [8000, 16000, 32000, 48000])->nullable();
        $table->unsignedTinyInteger('music_on_hold_chime_max')->nullable();

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX music on hold information');
    }
};
