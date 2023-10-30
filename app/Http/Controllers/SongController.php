<?php

namespace App\Http\Controllers;

use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;


class SongController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    private function getVideoIdFromLink($videoLink)
    {
        $url = parse_url($videoLink);
        if (isset($url['query'])) {
            parse_str($url['query'], $query);
            if (isset($query['v'])) {
                return $query['v'];
            }
        }

        return null;
    }
    private function downloadMp3FromYouTube($videoLink)
    {
        // Execute yt-dlp command to download audio
        $command = "yt-dlp --extract-audio --write-info-json --audio-format mp3 -o \"mp3/%(title)s.%(ext)s\" -- $videoLink";
        $output = shell_exec($command);

        echo('output: ' .  $output);

        // Parse the output to get the path to the downloaded MP3 file
        $matches = [];
        if (preg_match('/\[ExtractAudio\] Destination: (.*\.mp3)/', $output, $matches)) {
            $mp3Path = $matches[1];
            return $mp3Path;
        }



        // Return null if the path cannot be determined
        return null;
    }

    public function getVideoInfo(Request $request)
    {
        $videoId = $this->getVideoIdFromLink($request->videoLink);
        
        if (!$videoId) {
            return response()->json(['error' => 'Invalid YouTube video link'], 400);
        }

        $apiKey = config('services.google.api_key');
        $response = Http::withOptions(['verify' => false])->get("https://www.googleapis.com/youtube/v3/videos", [
            'part' => 'snippet',
            'id' => $videoId,
            'key' => $apiKey,
        ]);

        $videoInfo = $response->json();
        // Download and save the MP3 file using yt-dlp
        $mp3Path = $this->downloadMp3FromYouTube($request->videoLink);
        echo("mp3: " . $mp3Path);

        $song = new Song([
            'name' => $videoInfo['items'][0]['snippet']['title'],
            'description' => $videoInfo['items'][0]['snippet']['description'],
            'slug' => Str::slug($videoInfo['items'][0]['snippet']['title']),
            'author' => $videoInfo['items'][0]['snippet']['channelTitle'],
            'image' => 'path_to_image', // You can replace 'path_to_image' with the actual image path
            'mp3_path' => asset($mp3Path), // Store the path to the MP3 file
        ]);
    
        $song->save();

        return response()->json([
            "info" => $videoInfo,
            "link" => "link"], 200);
    }


    public function index(){
        try{
            $song = Song::query()->get();

            return response()->json($song);

        } catch(\Throwable $th){
            return response()->json(['message' => $th->getMessage()]);
        }
    }
    /* public function index()
    {
        $path = storage_path('app/public/mp3/Como Un Cristal.mp3');

        if (file_exists($path)) {
            return response()->file($path);
        }
    } */

    public function download()
    {
        try {
            $imgName = 'pingui.png';
            $link = asset("storage/app/public/$imgName");
            return Storage::download('pingui.png');
            //return response()->download($link);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'slug' => 'required|string|unique:files',
            'description' => 'required|string',
            'author' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'songFile' => 'required|mimes:mp3|max:20480',
        ]);

        // Handle image upload and store the path in the database
        $imagePath = $request->file('image')->store('images', 'public');

        // Handle MP3 file upload and store it as binary data in the database
        $songFileData = file_get_contents($request->file('songFile'));

        // Create a new file record
        $file = new Song([
            'name' => $request->input('name'),
            'slug' => $request->input('slug'),
            'description' => $request->input('description'),
            'author' => $request->input('author'),
            'image' => $imagePath,
            'songFile' => $songFileData,
        ]);

        $file->save();

        return response()->json(['message' => 'File uploaded successfully'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
