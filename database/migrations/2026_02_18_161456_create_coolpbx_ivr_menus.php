<?php

use App\Models\Domain;
use App\Models\IVRMenu;
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
        $tableName = IVRMenu::getTableName();
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
        $tableName = IVRMenu::getTableName();
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

        $table->uuid('ivr_menu_uuid')->nullable(false)->primary()->first();
        $table->uuid('domain_uuid')->nullable(false);
        $table->uuid('dialplan_uuid')->nullable(false);
        $table->uuid('ivr_menu_parent_uuid')->nullable(false);
        $table->string('ivr_menu_name', length: 255)->nullable(false);
        $table->string('ivr_menu_extension', length: 255)->nullable(false);
        $table->string('ivr_menu_language', length: 3)->nullable();
        $table->string('ivr_menu_dialect', length: 12)->nullable(false);
        $table->string('ivr_menu_voice', length: 255)->nullable();
        $table->string('ivr_menu_greet_long', length: 1024)->nullable(false);
        $table->string('ivr_menu_greet_short', length: 1024)->nullable();
        $table->string('ivr_menu_invalid_sound', length: 1024)->nullable();
        $table->string('ivr_menu_exit_sound', length: 1024)->nullable();
        $table->string('ivr_menu_pin_number', length: 20)->nullable();
        $table->string('ivr_menu_confirm_macro', length: 20)->nullable();
        $table->string('ivr_menu_confirm_key', length: 2)->nullable();
        $table->string('ivr_menu_tts_engine', length: 2)->nullable();
        $table->string('ivr_menu_tts_voice', length: 2)->nullable();
        $table->unsignedTinyInteger('ivr_menu_confirm_attempts')->default(1)->nullable(false);
        $table->unsignedTinyInteger('ivr_menu_timeout')->default(1)->nullable(false);
        $table->string('ivr_menu_exit_app', length: 255)->nullable();
        $table->string('ivr_menu_exit_data', length: 255)->nullable();
        $table->unsignedTinyInteger('ivr_menu_inter_digit_timeout')->default(2000)->nullable(false);
        $table->unsignedTinyInteger('ivr_menu_max_failures')->default(1)->nullable(false);
        $table->unsignedTinyInteger('ivr_menu_max_timeouts')->default(1)->nullable(false);
        $table->unsignedTinyInteger('ivr_menu_digit_len')->default(5)->nullable(false);
        $table->enum('ivr_menu_direct_dial', ['true','false'])->default('false')->nullable(false);
        $table->string('ivr_menu_ringback', length: 1024)->nullable();
        $table->string('ivr_menu_cid_prefix', length: 255)->nullable();
        $table->string('ivr_menu_context', length: 255)->nullable();
        $table->longText('ivr_menu_description')->nullable();
        $table->enum('ivr_menu_enabled', ['true','false'])->default('false')->nullable(false);

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX IVR menu information');

        $table->foreign('domain_uuid')->on(Domain::getTableName())->references('domain_uuid')->cascadeOnDelete()->cascadeOnUpdate();
    }
};
