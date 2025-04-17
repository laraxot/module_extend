<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//----- models -----
use Modules\Extend\Models\Cache as MyModel;


class CreateCacheTable extends Migration
{
    //protected $table = 'cache'; //name of pack +"_"+ name of table, but this is a standard table
    public function getTable(){
        return with(new MyModel())->getTable();
    }
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasTable($this->getTable())) {
            Schema::create($this->getTable(), function (Blueprint $table) {
                $table->string('key', 64)->unique();
                $table->text('value');
                $table->integer('expiration');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists($this->getTable());
    }
}
