<?php

use App\Models\DefaultSetting;
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
        $tableName = DefaultSetting::getTableName();
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
        $tableName = DefaultSetting::getTableName();
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

        $table->uuid('default_setting_uuid')->nullable(false)->primary()->first();
        $table->uuid('app_uuid')->nullable();
        $table->string('default_setting_category', length: 255)->nullable(false);
        $table->string('default_setting_subcategory', length: 255)->nullable(false)->comment('this field usually is the name of the setting');
        $table->string('default_setting_name', length: 255)->nullable(false)->comment('this field is the type of the setting');
        $table->string('default_setting_value', length: 255)->nullable(false);
        $table->unsignedTinyInteger('default_setting_order')->default(0)->nullable(false);
        $table->longText('default_setting_description')->nullable();
        $table->enum('default_setting_enabled', ['true','false'])->default('false')->nullable(false);

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX default setting detail information');
    }
};
