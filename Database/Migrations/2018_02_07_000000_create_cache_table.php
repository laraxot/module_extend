<?php



use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCacheTable extends Migration
{
    protected $table = 'cache'; //name of pack +"_"+ name of table, but this is a standard table

    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasTable($this->table)) {
            Schema::create($this->table, function (Blueprint $table) {
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
        Schema::dropIfExists($this->table);
    }
}
