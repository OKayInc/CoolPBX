<?php

use App\Models\DialplanDetail;
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
        $tableName = DialplanDetail::getTableName();
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
        $tableName = DialplanDetail::getTableName();
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

        $table->uuid('dialplan_detail_uuid')->nullable(false)->primary()->first();
        $table->uuid('dialplan_uuid')->nullable(false);
        $table->uuid('domain_uuid')->nullable(false);
        $table->enum('dialplan_detail_tag', ['condition','regex','action','anti-action'])->nullable(false);
        $table->string('dialplan_detail_type', length: 255)->nullable(false);
        $table->string('dialplan_detail_data', length: 255)->nullable();
        $table->enum('dialplan_detail_break', ['on-true','on-false','always','never'])->nullable();
        $table->enum('dialplan_detail_inline', ['true','false',])->default('false')->nullable();
        $talbe->unsignedBigInteger('dialplan_detail_group')->defauilt(0)->nullable(false);
        $talbe->unsignedBigInteger('dialplan_detail_order')->defauilt(0)->nullable(false);
        $table->enum('dialplan_detail_enabled', ['true','false'])->default('false')->nullable(false);

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX currency conversion rate information');
    }
};
