<?php

use App\Models\BillingProfile;
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
        $tableName = Billing::getTableName();
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
        $tableName = Billing::getTableName();
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

        $table->uuid('billing_uuid')->nullable(false)->primary()->first();
        $table->uuid('domain_uuid')->nullable(false);
        $table->uuid('parent_billing_uuid')->nullable(false);
        $table->uuid('contact_uuid_from')->nullable(false);
        $table->uuid('contact_uuid_to')->nullable(false);
        $table->enum('type', ['domain','authcode'])->default('domain')->nullable(false);
        $table->string('type_value', length: 255)->nullable(false);
        $table->enum('credit_type', ['prepaid','postpaid'])->default('prepaid')->nullable(false);
        $talbe->decimal('credit', total:11, places:6);
        $table->unsignedTinyInteger('billing_cycle')->nullable(false);
        $table->char('currency')->nullable(false);
        $table->unsignedTinyInteger('pay_days')->nullable(false);
        $table->string('lcr_profile', length: 255)->nullable(false);
        $talbe->decimal('balance', total:11, places:5)->default(0);
        $talbe->decimal('old_balance', total:11, places:5)->default(0);
        $table->uuid('referred_by_uuid')->nullable();
        $table->unsignedTinyInteger('referred_depth')->default(1)->nullable(false);
        $table->unsignedTinyInteger('referred_percentage')->default(0)->nullable(false);
        $table->unsignedBigInteger('whmcs_user_id')->nullable();
        $table->enum('force_postpaid_full_payment', ['true','false'])->default('false')->nullable(false);
        $talbe->decimal('max_rate', total:11, places:5)->default(9999.0);
        $talbe->decimal('auto_topup_minimum_balance', total:11, places:5)->nullable();
        $talbe->decimal('auto_topup_charge', total:11, places:5)->nullable();
        $table->longText('billing_notes')->nullable();

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX billing profile information');

        $table->foreign('domain_uuid')->on(Domain::getTableName())->references('domain_uuid')->cascadeOnDelete()->cascadeOnUpdate();
    }
};
