<?php

use App\Models\Domain;
use App\Models\User;
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
        $tableName = User::getTableName();
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
        $tableName = User::getTableName();
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

        $table->uuid('user_uuid')->nullable(false)->primary()->first();
        $table->uuid('domain_uuid')->nullable(false);
        $table->uuid('contact_uuid')->nullable();
        $table->string('username', length: 255)->nullable(false);
        $table->string('password', length: 60)->nullable(false);
        $table->string('salt', length: 255)->nullable();
        $table->string('user_email', length: 255)->nullable(false);
        $table->string('user_status', length: 255)->nullable();
        $table->string('api_key', length: 36)->nullable();
        $table->string('user_totp_secret', length: 255)->nullable(false);
        $table->enum('user_enabled', ['true','false'])->default('false')->nullable(false);
        $table->text('token')->nullable();
        $table->text('remember_token')->nullable();

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX user information');
    }
};
