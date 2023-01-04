<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddIndividualStudentSheetReportMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999861)->firstOrFail()->getKey(),
            'title' => 'Ficha individual do aluno',
            'description' => null,
            'link' => '/module/Reports/IndividualStudentSheet',
            'order' => 0,
            'old' => 999204,
            'process' => 999204,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999204)->delete();
    }
}
