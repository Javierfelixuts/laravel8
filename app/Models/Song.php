<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    protected $fillable = [
        'name', // Add 'name' to the fillable attributes
        'description',
        'slug',
        'author',
        'image',
        'published_at',
        'mp3_path',
    ];
    use HasFactory;
    protected $appends = ['file_url'];

    public function getFileUrlAttribute()
    {
        $id = $this->id;
        $url = url("/storage/attachments/" . ($this->is_tmp ? 'tmp/' : '') . $this->file);
        return $this->file ? $url : '';
    }
}
