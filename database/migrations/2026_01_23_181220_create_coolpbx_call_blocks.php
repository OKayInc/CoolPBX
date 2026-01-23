<?php

use App\Models\CallBlock;
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
        $tableName = CallBlock::getTableName();
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
        $tableName = CallBlock::getTableName();
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

        $table->uuid('call_block_uuid')->nullable(false)->primary()->first();
        $table->uuid('domain_uuid')->nullable(false);
        $table->enum('call_block_direction', ['inbound','outbound','local'])->default('inbound')->nullable(false);
        $table->uuid('extension_uuid')->nullable();
        $table->string('call_block_name', length: 255)->nullable(false);
        $talbe->unsignedTinyInteger('call_block_country_code')->nullable();
        $table->string('call_block_number', length: 20)->nullable(false);
        $talbe->unsignedBigInteger('call_block_count')->default(0)->nullable(false);
        $table->string('call_block_action', length: 255)->nullable();
        $table->string('call_block_app', length: 255)->nullable();
        $table->string('call_block_data', length: 255)->nullable();
        $table->enum('call_block_enabled', ['true','false'])->default('false')->nullable(false);
        $table->longText('call_block_description')->nullable();

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX call block information');
    }
};
