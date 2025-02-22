<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Cviebrock\EloquentSluggable\Sluggable;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'image',
        'title',
        'body',
        'slug',
        'author_id',
        'category_id',
    ];

    protected $with = ['author', 'category'];

    public function author(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo{
        return $this->belongsTo(Category::class);
    }

    public function scopeFilter(Builder $query, array $filters, bool $isDashboard = false): void {

        $query->when($filters['category'] ?? false, 
        fn ($query, $category) =>
            $query->whereHas('category', fn($query) => $query->where('slug', $category))
        );
    
        $query->when($filters['author'] ?? false, 
        fn ($query, $author) =>
            $query->whereHas('author', fn($query) => $query->where('username', $author))
        );
    
        if ($isDashboard && auth()->check()) {
            $query->where('author_id', auth()->user()->id);
        }
    
        $query->when($filters['search'] ?? false, fn($query, $search) =>
        $query->where('title', 'like', '%' . $search . '%')
              ->orWhere('content', 'like', '%' . $search . '%')
        );
    }

    public function getRouteKeyName() {
        return 'slug';
    }

    public function sluggable(): array {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    

}
