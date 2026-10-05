<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'file_name',
        'mime_type',
        'path',
        'disk',
        'size',
        'alt_text',
        'caption',
    ];

    /**
     * Get the full URL of the media file.
     *
     * Membaca URL dari disk tempat file disimpan (Storage::disk()->url())
     * alih-alih hard-code asset('storage/...'), agar tetap benar ketika
     * disk diganti dari 'public' lokal ke S3/MinIO sesuai stack produksi.
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }
}
