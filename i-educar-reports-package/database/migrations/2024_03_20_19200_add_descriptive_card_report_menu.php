<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddDescriptiveCardReportMenu extends Migration
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
            'title' => 'Parecer',
            'description' => null,
            'link' => '/module/Reports/ReportDescriptiveCard',
            'order' => 0,
            'old' => 999202,
            'process' => 19992021,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 19992021)->delete();
    }
}
