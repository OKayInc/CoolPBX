<?php

use App\Models\Gateway;
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
        $tableName = Gateway::getTableName();
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
        $tableName = Gateway::getTableName();
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

        $table->uuid('gateway_uuid')->nullable(false)->primary()->first();
        $table->uuid('domain_uuid')->nullable();
        $table->string('gateway', length: 255)->nullable(false);
        $table->string('username', length: 255)->nullable()->comment('Deprecated.');
        $table->string('password', length: 255)->nullable(false);
        $table->enum('distinct_to', ['true','false'])->default('false')->nullable(false);
        $table->string('auth_username', length: 255)->nullable();
        $table->string('realm', length: 255)->nullable();
        $table->string('from_user', length: 255)->nullable();
        $table->string('from_domain', length: 255)->nullable();
        $table->string('proxy', length: 255)->nullable();
        $table->string('register_proxy', length: 255)->nullable();
        $table->string('outbound_proxy', length: 255)->nullable();
        $table->unsignedBigInteger('expire_seconds')->default(800)->nullable(false);
        $table->enum('register', ['true','false'])->default('false')->nullable(false);
        $table->enum('register_transport', ['udp','tcp','tls'])->default('udp')->nullable(false);
        $table->string('contact_params', length: 255)->nullable();
        $table->unsignedBigInteger('retry_seconds')->default(30)->nullable(false);
        $table->string('extension', length: 255)->nullable();
        $table->unsignedTinyInteger('ping')->nullable();
        $table->unsignedTinyInteger('ping_min')->nullable();
        $table->unsignedTinyInteger('ping_max')->nullable();
        $table->enum('contact_in_ping', ['true','false'])->default('false')->nullable(false);
        $table->enum('caller_id_in_from', ['true','false'])->default('false')->nullable(false);
        $table->enum('supress_cng', ['true','false'])->default('false')->nullable(false);
        $table->enum('sip_cid_type', ['none','rpid','pid'])->default('rpid')->nullable(false);
        $table->string('codec_prefs', length: 255)->nullable();
        $table->unsignedBigInteger('channels')->default(0)->nullable(false);
        $table->enum('extension_in_contact', ['true','false'])->default('false')->nullable(false);
        $table->string('context', length: 255)->nullable(false);
        $table->string('profile', length: 255)->nullable(false);
        $table->string('hostname', length: 255)->nullable();
        $table->longText('description')->nullable();
        $table->enum('enabled', ['true','false'])->default('false')->nullable(false);

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX gateway information');

        $table->foreign('domain_uuid')->on(Domain::getTableName())->references('domain_uuid')->cascadeOnDelete()->cascadeOnUpdate()->nullable();
    }
};
