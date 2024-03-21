<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddStudentSignatureReportMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999300)->firstOrFail()->getKey(),
            'title' => 'Lista de alunos para assinatura',
            'description' => null,
            'link' => '/module/Reports/StudentSignature',
            'order' => 0,
            'parent_old' => 999300,
            'process' => 19998701,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 19998701)->delete();
    }
}
