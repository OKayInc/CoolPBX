<?php

use App\Models\Setting;
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
        $tableName = Setting::getTableName();
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
        $tableName = Setting::getTableName();
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

        $table->uuid('setting_uuid')->nullable(false)->primary()->first();
        $table->string('numbering_plan', length: 255)->nullable(false);
        $table->ipAddress('event_socket_ip_address')->default('127.0.0.1')->nullable(false);
        $table->unsignedTinyInteger('event_socket_port')->default(8021)->nullable(false);
        $table->string('event_socket_password', length: 255)->default('ClueCon')->nullable();
        $table->string('event_socket_acl', length: 255)->nullable();
        $table->unsignedTinyInteger('xml_rpc_http_port')->default(8080)->nullable(false);
        $table->string('xml_rpc_auth_realm', length: 255)->nullable(false);
        $table->string('xml_rpc_auth_user', length: 255)->nullable(false);
        $table->string('xml_rpc_auth_pass', length: 255)->nullable(false);
        $table->unsignedTinyInteger('admin_pin')->nullable();
        $table->enum('mod_shout_decoder', ['i486','i586','i686','amd64','generic'])->nullable();
        $table->decimal('mod_shout_volume', total: 3, places: 1)->default(0.3)->nullable(false);

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX setting information');
    }
};
