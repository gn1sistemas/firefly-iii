<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'clients',
            function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();
                $table->string('name', 255);
                $table->string('oauth_access_id', 255)->nullable();
                $table->string('oauth_secret_id', 255)->nullable();
                $table->string('oauth_redirect_url', 255)->nullable();
                $table->string('oauth_url_authorize', 255)->nullable();
                $table->string('oauth_url_access_token', 255)->nullable();
                $table->string('oauth_url_user', 255)->nullable();
            }
        );

        DB::table('clients')->insert(
            array(
                'name' => 'ABORL-CCF',
            )
        );

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients');
    }
}
