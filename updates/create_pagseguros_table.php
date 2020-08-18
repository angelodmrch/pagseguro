<?php namespace Dmrch\PagSeguro\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreatePagsegurosTable extends Migration
{
    public function up()
    {
        Schema::create('dmrch_pagseguro', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->text('items');
            $table->integer('user_id');
            $table->text('transaction_id');
            $table->integer('status')->default(0);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dmrch_pagseguro');
    }
}
