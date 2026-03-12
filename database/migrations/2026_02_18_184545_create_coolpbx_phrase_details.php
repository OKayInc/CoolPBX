<?php

use App\Models\Domain;
use App\Models\PhraseDetail;
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
        $tableName = PhraseDetail::getTableName();
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
        $tableName = PhraseDetail::getTableName();
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

        $table->uuid('phrase_detail_uuid')->nullable(false)->primary()->first();
        $table->uuid('domain_uuid')->nullable();
        $table->uuid('phrase_uuid')->nullable(false);
        $table->unsignedTinyInteger('phrase_detail_group')->default(0)->nullable(false);
        $table->string('phrase_detail_tag', length: 255)->nullable();
        $table->string('phrase_detail_pattern', length: 255)->nullable();
        $table->enum('phrase_detail_function', ['play-file','execute'])->nullable(false);
        $table->string('phrase_detail_data', length: 255)->nullable();
        $table->string('phrase_detail_method', length: 255)->nullable();
        $table->string('phrase_detail_type', length: 255)->nullable();
        $table->unsignedTinyInteger('phrase_detail_order')->default(0)->nullable(false);

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX phrase detail detail information');

        $table->foreign('domain_uuid')->on(Domain::getTableName())->references('domain_uuid')->cascadeOnDelete()->cascadeOnUpdate()->nullable();
    }
};
