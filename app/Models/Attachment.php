<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = ['file_url'];

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            // ... code here
        });

        self::created(function ($model) {
            // ... code here
        });

        self::updating(function ($model) {
            // ... code here
        });

        self::updated(function ($model) {
            // ... code here
        });

        self::deleting(function ($model) {
            // ... code here
            $folder = storage_path('app') . "/attachments/" . ($model->is_tmp ? 'tmp/' : '');
            if ($model->file && file_exists($folder . $model->file)) {
                unlink($folder . $model->file);
            }
        });

        self::deleted(function ($model) {
            // ... code here
        });
    }


    public function people()
    {
        return $this->belongsToMany(Person::class, 'person_attachments', 'attachment_id');
    }

    public function getFileUrlAttribute()
    {
        $id = $this->id;
        $url = url("/storage/attachments/" . ($this->is_tmp ? 'tmp/' : '') . $this->file);
        return $this->file ? $url : '';
    }
}