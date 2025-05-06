<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class ReportReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $reasons = ["Inappropriate Content", "Spam", "Hate Speech", "Post should not be flagged as Official", "Reason Not Listed"];

        foreach($reasons as $reason)
        {
            DB::table('report_reasons')->insert([
                'reason'     => $reason,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
