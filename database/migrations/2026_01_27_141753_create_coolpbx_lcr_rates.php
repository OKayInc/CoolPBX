<?php

use App\Models\Lcr;
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
        $tableName = Lcr::getTableName();
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
        }    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = Lcr::getTableName();
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

        $table->uuid('lcr_uuid')->nullable(false)->primary()->first();
        $table->uuid('carrier_uuid')->nullable(false);
        $table->string('digits', length: 255)->nullable(false);
        $table->string('origination_digits', length: 255)->nullable(false);
        $table->enum('lcr_direction', ['inbound','outbound','local'])->default('outbound')->nullable(false);
        $table->decimal('connect_rate', total: 11, places: 5)->nullable()->comment('first increment rate per minute');
        $table->decimal('rate', total: 11, places: 5)->nullable(false)->comment('other increment rate per minute');
        $table->tinyInteger('connect_increment')->default(1)->nullable()->comment('first increment lenght in seconds');
        $table->tinyInteger('talk_increment')->default(1)->nullable(false)->comment('other increment lenght in seconds');
        $table->decimal('intrastate_rate', total: 11, places: 5)->nullable()->comment('intra state rate per minute');
        $table->decimal('intralata_rate', total: 11, places: 5)->nullable()->comment('intra lata rate per minute');
        $table->string('currency', length: 3)->nullable(false);
        $table->tinyInteger('lead_strip')->default(0)->nullable(false);
        $table->tinyInteger('trail_strip')->default(0)->nullable(false);
        $table->string('prefix', length: 255)->nullable();
        $table->string('suffix', length: 255)->nullable();
        $table->string('lcr_profile', length: 255)->nullable();
        $table->date('date_start')->nullable();
        $table->date('date_end')->nullable();
        $table->decimal('quality', total: 10, places: 6)->nullable();
        $table->decimal('reliability', total: 10, places: 6)->nullable();
        $table->string('cid', length: 255)->nullable();
        $table->text('description')->nullable();
        $table->enum('enabled', ['true','false'])->default('false');

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX lcr rates information');
    }
};
