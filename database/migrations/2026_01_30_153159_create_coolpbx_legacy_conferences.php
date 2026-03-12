<?php

use App\Models\Conference;
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
        $tableName = Conference::getTableName();
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
        $tableName = Conference::getTableName();
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

        $table->uuid('conference_uuid')->nullable(false)->primary()->first();
        $table->uuid('domain_uuid')->nullable(false);
        $table->uuid('dialplan_uuid')->nullable(false);
        $table->string('conference_name', length: 255)->nullable(false);
        $table->string('conference_extension', length: 255)->nullable(false);
        $table->string('conference_pin_number', length: 255)->nullable();
        $table->string('conference_profile', length: 255)->default('default')->nullable(false);
        $table->string('conference_flags', length: 255)->nullable();
        $table->string('conference_email_address', length: 255)->nullable();
        $table->string('conference_account_code', length: 255)->nullable();
        $table->unsignedTinyInteger('conference_order')->default(0)->nullable(false);
        $table->longText('conference_description')->nullable();
        $table->enum('conference_enabled', ['true','false'])->default('false')->nullable(false);

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX legacy conference information');

        $table->foreign('domain_uuid')->on(Domain::getTableName())->references('domain_uuid')->cascadeOnDelete()->cascadeOnUpdate();
    }
};
