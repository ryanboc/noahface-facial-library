<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AwardSeeder extends Seeder
{
    public function run(): void
    {
        // --- 1. Poultry Processing Award ---
        $poultryId = DB::table('awards')->insertGetId([
            'name' => 'Poultry Processing Award',
            'pay_guide_link' => 'https://calculate.fairwork.gov.au/payguides/fairwork/ma000074/docx', // Cleaned link from 
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Conditions 
        DB::table('award_conditions')->insert([
            'award_id' => $poultryId,
            'hours_per_day_rule' => '10 hours - Can be extended to 12 hours with an agreement',
            'leading_hand_allowance' => 'Leading Hand 1-19 emp-$0.99/hr',
            'meal_allowance' => 'Meal Allowance $17.92 at 11.5hrs',
            'paid_break_rule' => '20 min for 6 hours',
            'unpaid_break_rule' => '30 min by 6 hours of Commencing Work',
            'remarks' => 'OT calculated over 152+',
        ]);

        // Rates 
        $poultryRates = [
            ['Casual', 'Overtime', '120%'],
            ['Casual', 'Public Holiday', '200%'],
            ['Casual', 'Early Morning', '108%'],
            ['Casual', 'Night', '112%'],
            ['Casual', 'Saturday', '120%'],
            ['Casual', 'Sunday', '140%'],
            ['Full Time/Part Time', 'Overtime', '150%'],
            ['Full Time/Part Time', 'Overtime after 3hrs', '200%'],
            ['Full Time/Part Time', 'Public Holiday', '250%'],
        ];

        foreach ($poultryRates as $rate) {
            DB::table('award_rates')->insert([
                'award_id' => $poultryId,
                'employment_type' => $rate[0],
                'category' => $rate[1],
                'rate_value' => $rate[2],
            ]);
        }

        // --- 6. Storage Services & Wholesale Award ---
        // Highlighting this one because of the typo in the source document
        $storageId = DB::table('awards')->insertGetId([
            'name' => 'Storage Services & Wholesale Award',
            'pay_guide_link' => 'https://calculate.fairwork.gov.au/payguides/fairwork/ma000084/pdf', // 
            'created_at' => now(), 
            'updated_at' => now(),
        ]);

        DB::table('award_conditions')->insert([
            'award_id' => $storageId,
            'hours_per_day_rule' => '10 hours',
            'leading_hand_allowance' => 'NA',
            'meal_allowance' => 'NA', 
            'paid_break_rule' => 'afternoon 10min each morning and', 
            'unpaid_break_rule' => '30 min by 5 hours of Ordinary Hrs',
            'remarks' => 'OT calculated over 152+',
        ]);

        // Note: The document lists "22016" for Casual Public Holiday. 
        // You may want to correct this to '250%' manually.
        $storageRates = [
            ['Casual', 'Overtime', '140%'],
            ['Casual', 'Public Holiday', '22016'], // Kept raw data as per source
            ['Full Time/Part Time', 'Overtime', '150%'],
            ['Full Time/Part Time', 'Public Holiday', '250%'],
        ];

        foreach ($storageRates as $rate) {
            DB::table('award_rates')->insert([
                'award_id' => $storageId,
                'employment_type' => $rate[0],
                'category' => $rate[1],
                'rate_value' => $rate[2],
            ]);
        }
    }
}