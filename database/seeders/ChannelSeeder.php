<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('channels')->insert([
            'name'       => 'Movies',
            'icon'       => 'https://breakdown-bucket.s3.amazonaws.com/images/icons/movies.png',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        DB::table('channels')->insert([
            'name'       => 'TV',
            'icon'       => 'https://breakdown-bucket.s3.amazonaws.com/images/icons/tv.png',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()

        ]);
        DB::table('channels')->insert([
            'name' => 'Books',
            'icon'       => 'https://breakdown-bucket.s3.amazonaws.com/images/icons/books.png',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }
}
