<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $awardId = DB::table('awards')
            ->where('name', 'Poultry Processing Award')
            ->value('id');

        if (! $awardId) {
            return;
        }

        DB::table('award_rates')->updateOrInsert(
            ['award_id' => $awardId, 'employment_type' => 'Casual', 'category' => 'Night'],
            ['rate_value' => '140%']
        );

        DB::table('award_rates')->updateOrInsert(
            ['award_id' => $awardId, 'employment_type' => 'Full Time/Part Time', 'category' => 'Night'],
            ['rate_value' => '115%']
        );
    }

    public function down(): void
    {
        $awardId = DB::table('awards')
            ->where('name', 'Poultry Processing Award')
            ->value('id');

        if (! $awardId) {
            return;
        }

        DB::table('award_rates')
            ->where('award_id', $awardId)
            ->where('employment_type', 'Casual')
            ->where('category', 'Night')
            ->update(['rate_value' => '112%']);

        DB::table('award_rates')
            ->where('award_id', $awardId)
            ->where('employment_type', 'Full Time/Part Time')
            ->where('category', 'Night')
            ->delete();
    }
};
