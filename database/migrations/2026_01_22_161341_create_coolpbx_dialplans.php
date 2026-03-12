<?php

use App\Models\Dialplan;
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
        $tableName = Dialplan::getTableName();
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
        $tableName = Dialplan::getTableName();
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

        $table->uuid('dialplan_uuid')->nullable(false)->primary()->first();
        $table->uuid('domain_uuid')->nullable();
        $table->uuid('app_uuid')->nullable(false);
        $table->string('hostname', length: 255)->nullable();
        $table->string('dialplan_context', length: 255)->nullable(false);
        $table->string('dialplan_name', length: 255)->nullable(false);
        $table->enum('dialplan_destination', ['true','false'])->default('false')->nullable(false);
        $table->enum('dialplan_continue', ['true','false'])->default('false')->nullable(false);
        $table->longText('dialplan_xml')->nullable();
        $talbe->unsignedInteger('dialplan_order')->default(200)->nullable(false);
        $table->enum('dialplan_enabled', ['true','false'])->default('false')->nullable(false);
        $table->longText('dialplan_description')->nullable();

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX dialplan information');

        $table->foreign('domain_uuid')->on(Domain::getTableName())->references('domain_uuid')->cascadeOnDelete()->cascadeOnUpdate()->nullable();
    }
};
