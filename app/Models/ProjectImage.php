<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_name'
    ];

    protected $appends = ['image_path'];

    public function getImagePathAttribute()
    {
        if (!$this->image_name) {
            return null;
        }

        return route('secure-assets').'/projects/'.$this->image_name;
    }
}
