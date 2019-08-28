<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//----- models -----
use Modules\Extend\Models\Session as MyModel;


class CreateSessionsTable extends Migration
{
    //protected $table = 'sessions'; //name of pack +"_"+ name of table, but this is a standard table
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
                $table->string('id', 191)->unique();
                $table->unsignedInteger('user_id')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->text('payload');
                $table->integer('last_activity');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasTable($this->getTable())) {
            Schema::dropIfExists($this->getTable());
        }
    }
}
