<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Spatie\Activitylog\Models\Activity;

class AddBusinessIdToActivityLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->integer('business_id')->after('subject_type')->nullable();
        });

        $activites = Activity::with(['causer'])->groupBy('causer_id')->get();

        foreach ($activites as $activity) {
            Activity::where('causer_id', $activity->causer_id)
                ->update(['business_id' => $activity->causer->business_id ?? null]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
