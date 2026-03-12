<?php

use App\Models\DeviceLine;
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
        $tableName = DeviceLine::getTableName();
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
        $tableName = DeviceLine::getTableName();
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

        $table->uuid('device_line_uuid')->nullable(false)->primary()->first();
        $table->uuid('domain_uuid')->nullable(false);
        $table->uuid('device_uuid')->nullable(false);
        $table->unsignedTinyInteger('line_number')->nullable(false);
        $table->string('server_address', length: 255)->nullable();
        $table->string('server_address_primary', length: 255)->nullable();
        $table->string('server_address_secondary', length: 255)->nullable();
        $table->string('outbound_proxy_primary', length: 255)->nullable();
        $table->string('outbound_proxy_secondary', length: 255)->nullable();
        $table->string('label', length: 255)->nullable();
        $table->string('display_name', length: 255)->nullable();
        $table->string('user_id', length: 255)->nullable();
        $table->string('auth_id', length: 255)->nullable();
        $table->string('password', length: 255)->nullable();
        $table->unsignedTinyInteger('sip_port')->default(5060)->nullable(false);
        $table->enum('sip_transport', ['tcp','udp','tls','dns srv'])->default('udp')->nullable(false);
        $table->unsignedTinyInteger('register_expires')->default(120)->nullable(false);
        $table->string('shared_line', length: 255)->nullable();
        $table->enum('enabled', ['true','false'])->default('false')->nullable(false);

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX device line detail information');

        $table->foreign('domain_uuid')->on(Domain::getTableName())->references('domain_uuid')->cascadeOnDelete()->cascadeOnUpdate();
    }
};
