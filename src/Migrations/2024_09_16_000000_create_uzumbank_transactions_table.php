<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUzumbankTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uzumbank_transactions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('uzumbank_service_id')->nullable();
            $table->string('uzumbank_trans_id')->nullable();
            $table->bigInteger('uzumbank_timestamp')->nullable();
            $table->bigInteger('uzumbank_amount')->nullable();
            $table->string('uzumbank_payment_source')->nullable();
            $table->string('uzumbank_tariff')->nullable();
            $table->string('uzumbank_processing_reference_number')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('reversed_at')->nullable();
            $table->mediumText('params')->nullable();
            $table->morphs('payable');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('uzumbank_transactions');
    }
}
