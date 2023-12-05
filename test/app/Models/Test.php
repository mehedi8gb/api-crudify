<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cviebrock\EloquentSluggable\Sluggable;

class Test extends Model
{
use SoftDeletes, HasFactory, Sluggable;

    protected $table = 'tests'; // Table name if different from model name

    protected $primaryKey = 'id'; // Primary key field

    protected $fillable = [
        'title',
        'slug',
        // Add other attributes that can be mass-assigned here
    ];

    protected $guarded = [
        // 'admin_only_field', // Add attributes that should not be mass-assigned here
    ];

    protected array $dates = [
        'created_at',
        'updated_at',
        // dates is an array of fields that should be cast to dates
    ];

    protected $casts = [
        'title' => 'encrypted', // Cast 'title' attribute to encrypted
        // Add other attribute casting here
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => ['title', 'id'], // Generate slug from 'title' and 'id' attributes
                'onUpdate' => true,          // Regenerate slug when the title is updated
            ],
        ];
    }
}
        