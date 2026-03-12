<?php

use App\Models\DeviceKey;
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
        $tableName = DeviceKey::getTableName();
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
        $tableName = DeviceKey::getTableName();
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

        $table->uuid('device_key_uuid')->nullable(false)->primary()->first();
        $table->uuid('domain_uuid')->nullable(false);
        $table->uuid('device_uuid')->nullable(false);
        $table->unsignedTinyInteger('device_key_id')->nullable(false);
        $table->enum('device_key_category', ['line','memory','programmable','expansion'])->nullable(false);
        $table->string('device_key_vendor', length: 255)->nullable();
        $table->string('device_key_type', length: 255)->nullable();
        $table->string('device_key_subtype', length: 255)->nullable();
        $table->unsignedTinyInteger('device_key_line')->nullable();
        $table->string('device_key_value', length: 255)->nullable();
        $table->string('device_key_extension', length: 255)->nullable();
        $table->enum('device_key_protected', ['true','false'])->default('false')->nullable(false);
        $table->string('device_key_label', length: 255)->nullable();
        $table->string('device_key_icon', length: 255)->nullable();

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX device key detail information');

        $table->foreign('domain_uuid')->on(Domain::getTableName())->references('domain_uuid')->cascadeOnDelete()->cascadeOnUpdate();
    }
};
