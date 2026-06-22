<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'first_name',
        'last_name',
        'gender',
        'email',
        'tel',
        'address',
        'building',
        'detail',
    ];

    //リレーション定義
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class)
            ->withTimestamps();
    }

    //一覧取得用検索ロジック
    public function scopeKeyword($query, $keyword)
    {
        if (!$keyword) {
            return;
        }

        $query->where(function ($q) use ($keyword) {
            $q->where('first_name', 'like', "%{$keyword}%")
                ->orWhere('last_name', 'like', "%{$keyword}%")
                ->orWhere('email', 'like', "%{$keyword}%");
        });
    }

    public function scopeGender($query, $gender)
    {
        if (!$gender || $gender == 0) {
            return;
        }

        $query->where('gender', $gender);
    }

    public function scopeCategory($query, $categoryId)
    {
        if (!$categoryId) {
            return;
        }

        $query->where('category_id', $categoryId);
    }

    public function scopeDate($query, $date)
    {
        if (!$date) {
            return;
        }

        $query->whereDate('created_at', $date);
    }
}
