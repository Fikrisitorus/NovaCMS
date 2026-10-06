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

    /**
     * Scope: cari media berdasarkan kata kunci pada name, alt_text,
     * caption, dan file_name. Pencarian case-insensitive dengan LIKE
     * (portabel SQLite/PostgreSQL).
     */
    public function scopeSearch($query, ?string $q)
    {
        if (blank($q)) {
            return $query;
        }

        return $query->where(function ($query) use ($q) {
            $query->where('name', 'like', "%{$q}%")
                ->orWhere('alt_text', 'like', "%{$q}%")
                ->orWhere('caption', 'like', "%{$q}%")
                ->orWhere('file_name', 'like', "%{$q}%");
        });
    }
}
