<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class ChatGalleryBucketTag extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tags = ["Premier", "Director's Cut", "How-to","Watch Party", "Backstory", "Writer's Block",
                "Behind the Scenes", "Interview", "General"];
        foreach($tags as $tag)
        {
            DB::table('tags')->insert([
                'name'       => $tag,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
        
        
    }
}
