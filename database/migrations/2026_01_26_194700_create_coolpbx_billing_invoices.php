<?php

use App\Models\BillingInvoice;
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
        $tableName = BillingInvoice::getTableName();
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
        $tableName = BillingInvoice::getTableName();
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

        $table->uuid('billing_invoice_uuid')->nullable(false)->primary()->first();
        $table->uuid('billing_uuid')->nullable(false);
        $table->uuid('domain_uuid')->nullable(false);
        $table->uuid('payer_uuid')->nullable(false)->comment('user_uuid');
        $table->date('billing_payment_date')->nullable(false);
        $table->tinyInteger('settled')->default(0);
        $table->decimal('amount', total: 10, places: 2)->default(0.0)->nullable(false);
        $table->decimal('tax', total: 10, places: 2)->default(0.0)->nullable(false);
        $table->decimal('debt', total: 11, places: 5)->default(0.0)->nullable(false);
        $table->longText('post_payload')->nullable();
        $table->string('plugin_used', length: 255)->nullable(false);

        // timestamps
        $table->date('insert_date')->nullable();
        $table->uuid('insert_user')->nullable();
        $table->date('update_date')->nullable();
        $table->uuid('update_user')->nullable();
        $table->comment('CoolPBX billing profile information');
    }
};
