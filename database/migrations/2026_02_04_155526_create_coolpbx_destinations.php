<?php

use App\Models\Destination;
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
        $tableName = Destination::getTableName();
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
        $tableName = Destination::getTableName();
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

        $table->uuid('destination_uuid')->nullable(false)->primary()->first();
        $table->uuid('domain_uuid')->nullable(false);
        $table->uuid('dialplan_uuid')->nullable(false);
        $table->uuid('fax_uuid')->nullable();
        $table->uuid('user_uuid')->nullable();
        $table->uuid('group_uuid')->nullable();
        $table->enum('destination_type', ['inbound','outbound'])->default('inbound')->nullable(false);
        $table->string('destination_number', length: 255)->nullable(false);
        $table->string('destination_trunk_prefix', length: 255)->nullable();
        $table->string('destination_area_code', length: 255)->nullable();
        $table->string('destination_prefix', length: 255)->nullable();
        $table->string('destination_condition_field', length: 255)->nullable();
        $table->string('destination_number_regex', length: 255)->nullable();
        $table->string('destination_caller_id_name', length: 255)->nullable();
        $table->string('destination_caller_id_number', length: 25)->nullable();
        $table->string('destination_cid_name_prefix', length: 255)->nullable();
        $table->string('destination_context', length: 255)->default('public')->nullable(false);
        $table->enum('destination_record', ['true','false'])->default('false')->nullable(false);
        $table->string('destination_hold_music', length: 255)->nullable();
        $table->string('destination_distinctive_ring', length: 255)->nullable();
        $table->string('destination_accountcode', length: 255)->nullable();
        $table->unsignedTinyInteger('destination_type_voice')->default(0)->nullable(false);
        $table->unsignedTinyInteger('destination_type_fax')->default(0)->nullable(false);
        $table->unsignedTinyInteger('destination_type_emergency')->default(0)->nullable(false);
        $table->unsignedTinyInteger('destination_type_text')->default(0)->nullable(false);
        $table->longText('destination_conditions')->nullable();
        $table->json('destination_actions')->nullable();
        $table->string('destination_app', length: 255)->nullable();
        $table->string('destination_data', length: 255)->nullable();
        $table->string('destination_alternate_app', length: 255)->nullable();
        $table->string('destination_alternate_data', length: 255)->nullable();
        $table->unsignedTinyInteger('destination_order')->default(100)->nullable(false);
        $table->longText('destination_description')->nullable();
        $table->enum('destination_enabled', ['true','false'])->default('false')->nullable(false);

        // Billing related fields
        $table->string('currency', length: 3)->nullable()->comment('currency for selling');
        $table->string('currency_buy', length: 3)->nullable();
        $table->decimal('destination_sell', total: 10, places: 6)->default(0.0)->nullable();
        $table->decimal('destination_buy', total: 10, places: 6)->default(0.0)->nullable();
        $table->uuid('carrier_uuid')->nullable();
        $table->string('destination_carrier', length: 255)->nullable()->comment('deprecated');

        $table->string('default_setting_category', length: 255)->nullable(false);
        $table->string('default_setting_subcategory', length: 255)->nullable(false)->comment('this field usually is the name of the setting');
        $table->string('default_setting_name', length: 255)->nullable(false)->comment('this field is the type of the setting');
        $table->string('default_setting_value', length: 255)->nullable(false);
        $table->unsignedTinyInteger('transaction_code')->default(0)->nullable(false);
        $table->longText('default_setting_description')->nullable();
        $table->enum('default_setting_enabled', ['true','false'])->default('false')->nullable(false);

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX destination detail information');

        $table->foreign('domain_uuid')->on(Domain::getTableName())->references('domain_uuid')->cascadeOnDelete()->cascadeOnUpdate();
    }
};
