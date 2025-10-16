<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Post Comment Model
 * 
 * Handles blog post comments and replies
 */
class PostComment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'post_id',
        'comment',
        'replied_comment',
        'parent_id',
        'status'
    ];

    /**
     * Get the user who wrote the comment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user_info()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * Get all comments with pagination.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getAllComments()
    {
        return self::with('user_info')->paginate(10);
    }

    /**
     * Get all user comments with pagination.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getAllUserComments()
    {
        return self::where('user_id', auth()->user()->id)->with('user_info')->paginate(10);
    }

    /**
     * Get the post that owns the comment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id', 'id');
    }

    /**
     * Get comment replies.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function replies()
    {
        return $this->hasMany(PostComment::class, 'parent_id')->where('status', 'active');
    }
}
