<?php

use App\Models\Role;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateRolesTable extends Migration
{
    public function up()
    {
        DB::beginTransaction();

        try {
            $this->createTable();
            $this->addRoles();
        } catch (\Exception $e) {
            DB::rollback();
        }

        DB::commit();
    }

    public function down()
    {
        DB::beginTransaction();

        Schema::drop('roles');

        DB::commit();
    }

    public function createTable()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });
    }

    public function addRoles()
    {
        DB::table('roles')->insert([
            [
                'id' => Role::ADMIN,
                'name' => 'administrator'
            ],
            [
                'id' => Role::USER,
                'name' => 'user'
            ]
        ]);
    }
}
