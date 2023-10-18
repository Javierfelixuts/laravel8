<?php

namespace App\Http\Controllers;

use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

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
        $command = "yt-dlp --extract-audio --audio-format mp3 -o \"mp3/%(title)s.%(ext)s\" -- $videoLink";
        $output = shell_exec($command);

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
        $link = asset("storage/app/public/".$mp3Path);

        $contents = Storage::get('Como Un Cristal.mp3');
        echo("contents: ". $contents);
        if ($mp3Path) {
            Storage::put('public/mp3/' . $videoId . '.mp3', file_get_contents($mp3Path));
            // Save the MP3 file to the storage folder
            echo("ok");
            // Optionally, you can save the MP3 file path to your database or use it as needed
        } else {
            return response()->json(['error' => "Failed to download MP3"], 500);
        }

        return response()->json([
            "info" => $videoInfo,
            "link" => $link], 200);
    }
    public function index()
    {
        $query = Song::query();
        $imgName = 'pingui.png';

        $link = asset("storage/$imgName");
        $contents = Storage::get('mp3/Como Un Cristal.mp3');
        Storage::move('old/file.jpg', 'new/file.jpg');
        return response()->json(["data" => $query->get(), "link" => $link, "contents" => $contents]);
    }

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
