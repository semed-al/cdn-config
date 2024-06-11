<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddConceptualCardReportMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999450)->firstOrFail()->getKey(),
            'title' => 'Conceitual',
            'description' => null,
            'link' => '/module/Reports/ReportConceptualCard',
            'order' => 0,
            'old' => 19992022,
            'process' => 19992022,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 19992022)->delete();
    }
}