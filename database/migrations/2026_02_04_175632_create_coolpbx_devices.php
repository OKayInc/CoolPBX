<?php

use App\Models\Device;
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
        $tableName = Device::getTableName();
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
        $tableName = Device::getTableName();
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

        $table->uuid('device_uuid')->nullable(false)->primary()->first();
        $table->uuid('domain_uuid')->nullable(false);
        $table->uuid('device_profile_uuid')->nullable();
        $table->uuid('device_user_uuid')->nullable();
        $table->uuid('device_uuid_alternate')->nullable();
        $table->macAddress('device_mac_address')->nullable();
        $table->string('device_label', length: 255)->nullable(false);
        $table->string('device_vendor', length: 255)->nullable(false);
        $table->string('device_location', length: 255)->nullable(false);
        $table->string('device_model', length: 255)->nullable(false);
        $table->string('device_firmware_version', length: 255)->nullable(false);
        $table->enum('device_enabled', ['true','false'])->default('false')->nullable(false);
        $table->date('device_enabled_date')->nullable();
        $table->string('device_template', length: 255)->nullable();
        $table->string('device_username', length: 255)->nullable();
        $table->string('device_password', length: 255)->nullable();
        $table->longText('device_description')->nullable();
        $table->date('device_provisioned_date')->nullable();
        $table->enum('device_provisioned_method', ['http','https'])->nullable();
        $table->ipAddress('device_provisioned_ip')->nullable(false);
        $table->string('device_provisioned_agent', length: 255)->nullable(false);

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX device detail information');
    }
};
