<?php

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\MenuLanguage;
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
        $tableName = MenuLanguage::getTableName();
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
        $tableName = MenuLanguage::getTableName();
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

        $table->uuid('menu_language_uuid')->nullable(false)->primary()->first();
        $table->uuid('menu_uuid')->nullable(false);
        $table->uuid('menu_item_uuid')->nullable(false);
        $table->string('menu_language', length: 255)->nullable(false);
        $table->string('menu_item_title', length: 255)->nullable();

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX menu language information');

        $table->foreign('menu_uuid')->on(Menu::getTableName())->references('menu_uuid')->cascadeOnDelete()->cascadeOnUpdate();
        $table->foreign('menu_item_uuid')->on(MenuItem::getTableName())->references('menu_item_uuid')->cascadeOnDelete()->cascadeOnUpdate();
    }
};
