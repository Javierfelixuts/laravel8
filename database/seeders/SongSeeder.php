<?php

namespace Database\Seeders;

use App\Models\Song;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SongSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $string = file_get_contents(base_path()."/database/seeders/countries.json");
        $json_array = json_decode($string, true);

        foreach($json_array as $value){
            $value = (array)$value;
            Song::updateOrCreate([
                'iso' => $value['iso']
            ],[
                'id' => $value['id'],
                'name' => $value['name'],
                'description' => $value['description'],
                'slug' => $value['slug'],
                'author' => $value['author'],
                'image' => $value['image'],
                'duration' => $value['duration'],
                'duration_string' => $value['duration_string'],
                'published_at' => $value['published_at'],
                'mp3_path' => $value['mp3_path'],
                'json_info' => $value['json_info'],
            ]);
        }  
    }
}
