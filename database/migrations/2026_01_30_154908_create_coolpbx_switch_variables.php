<?php

use App\Models\Variable;
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
        $tableName = Variable::getTableName();
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
        $tableName = Variable::getTableName();
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

        $table->uuid('var_uuid')->nullable(false)->primary()->first();
        $table->string('var_category', length: 255)->nullable(false);
        $table->string('var_name', length: 255)->nullable(false);
        $table->string('var_value', length: 255)->nullable(false);
        $table->enum('var_command', ['set','exec-set'])->default('set')->nullable(false);
        $table->string('var_hostname', length: 255)->nullable();
        $table->unsignedTinyInteger('var_order')->default(0)->nullable(false);
        $table->enum('var_enabled', ['true','false'])->default('false')->nullable(false);
        $table->longText('var_description')->nullable();

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX switch variable information');
    }
};
