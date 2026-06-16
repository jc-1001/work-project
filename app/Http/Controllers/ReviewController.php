<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewImage;
use App\Models\ReviewVote;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request, $productId)
    {
        $rating = $request->input('rating');

        $userId = auth()->id();

        $reviews = Review::with(['user:id,name', 'images', 'votes'])
            ->where('product_id', $productId)
            ->when($rating, fn($q) => $q->where('rating', $rating))
            ->latest()
            ->paginate(10);

        foreach ($reviews as $review) {
            $review->up_count   = $review->votes->where('type', 'up')->count();
            $review->down_count = $review->votes->where('type', 'down')->count();
            $review->my_vote    = $userId ? $review->votes->where('user_id', $userId)->value('type') : null;
            $review->makeHidden('votes');
        }

        $stats = Review::where('product_id', $productId)
            ->selectRaw('
                COUNT(*) as total,
                ROUND(AVG(CAST(rating AS FLOAT)), 1) as avg_rating,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as star5,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as star4,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as star3,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as star2,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as star1
            ')
            ->first();

        return response()->json([
            'reviews' => $reviews,
            'stats'   => $stats,
        ]);
    }

    public function vote(Request $request, $reviewId)
    {
        $request->validate(['type' => 'required|in:up,down']);

        $userId   = auth()->id();
        $existing = ReviewVote::where('review_id', $reviewId)->where('user_id', $userId)->first();

        if ($existing) {
            if ($existing->type === $request->type) {
                $existing->delete();
                $action = 'cancelled';
            } else {
                $existing->update(['type' => $request->type]);
                $action = 'updated';
            }
        } else {
            ReviewVote::create(['review_id' => $reviewId, 'user_id' => $userId, 'type' => $request->type]);
            $action = 'created';
        }

        $counts = ReviewVote::where('review_id', $reviewId)
            ->selectRaw("SUM(CASE WHEN type = 'up' THEN 1 ELSE 0 END) as up_count, SUM(CASE WHEN type = 'down' THEN 1 ELSE 0 END) as down_count")
            ->first();

        return response()->json([
            'action'     => $action,
            'up_count'   => (int) $counts->up_count,
            'down_count' => (int) $counts->down_count,
        ]);
    }

    public function store(Request $request, $productId)
    {
        $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'content' => 'nullable|string|max:1000',
            'images'  => 'nullable|array|max:5',
            'images.*' => 'image|max:2048',
        ]);

        $userId = auth()->id();

        if (Review::where('product_id', $productId)->where('user_id', $userId)->exists()) {
            return response()->json(['message' => '您已評論過此商品'], 422);
        }

        $review = Review::create([
            'product_id' => $productId,
            'user_id'    => $userId,
            'rating'     => $request->rating,
            'content'    => $request->content,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('reviews', 'public');
                ReviewImage::create([
                    'review_id' => $review->id,
                    'path'      => $path,
                ]);
            }
        }

        return response()->json(['message' => '評論已送出', 'review' => $review->load('images')], 201);
    }
}
