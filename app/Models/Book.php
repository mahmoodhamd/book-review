<?php

namespace App\Models;




use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class Book extends Model
{
    use HasFactory;

    public function reviews(){
        return $this->hasMany(Review::class);
    }

    public function scopeTitle(Builder $query, string $title): Builder
    {
          return $query->where('title','LIKE', '%' . $title . '%');
    }
    public function scopeAuthor(Builder $query, string $author): Builder
    {
          return $query->where('author','LIKE', '%' . $author . '%');
    }

    public function scopePopular(Builder $query, $from=null, $to=null): Builder|QueryBuilder
    {
        return $query->withCount([
            'reviews'=>fn (Builder $q)=> $this->dateRangeFilter($q, $from, $to)
        ])

        ->orderBy('reviews_count','desc');

    }


    public function scopeHighestRated(Builder $query, $from ,$to): Builder|QueryBuilder
    {
        return $query->withAvg([
            'reviews'=>fn (Builder $q)=> $this->dateRangeFilter($q, $from, $to)
        ],'rating')
        ->orderBy('reviews_avg_rating','desc');
    }

    public function scopeMinReviews(Builder $query, int $minReviews): Builder|QueryBuilder{
     return $query->having('reviews_count', '>=' , $minReviews);
    }

    private function dateRangeFilter(Builder $query, $from=null, $to=null)
    {

        if($from && !$to){
            $query->where('created_at', '>=', $from);
        }else if(!$from && $to){
            $query->where('created_at', '<=', $to);
        }else if($from && $to){
           $query->whereBetween('created_at',[$from, $to]);
        }
    }

}
