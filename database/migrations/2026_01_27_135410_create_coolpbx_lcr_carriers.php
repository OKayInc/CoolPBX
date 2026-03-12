<?php

use App\Models\Carrier;
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
        $tableName = Carrier::getTableName();
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
        $tableName = Carrier::getTableName();
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

        $table->uuid('carrier_uuid')->nullable(false)->primary()->first();
        $table->string('carrier_name', length: 255)->nullable(false);
        $talbe->unsignedInteger('carrier_channels')->default(1)->nullable(false);
        $talbe->unsignedInteger('priority')->default(1)->nullable(false);
        $table->enum('fax_enabled', ['true','false'])->default('false')->nullable(false);
        $table->enum('short_call_friendly', ['true','false'])->default('false')->nullable(false);
        $table->decimal('cancellation_ratio', total: 6, places: 2)->default(100.0)->nullable(false);
        $table->text('lcr_tags')->nullable();
        $table->enum('enabled', ['true','false'])->default('false')->nullable(false);

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX carriers information');

        $table->foreign('domain_uuid')->on(Domain::getTableName())->references('domain_uuid')->cascadeOnDelete()->cascadeOnUpdate();
    }
};
