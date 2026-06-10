<?php

namespace App\Http\Controllers;

use App\Models\Review;
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

        $reviews->getCollection()->transform(function ($review) use ($userId) {
            $review->up_count   = $review->votes->where('type', 'up')->count();
            $review->down_count = $review->votes->where('type', 'down')->count();
            $review->my_vote    = $userId ? $review->votes->where('user_id', $userId)->value('type') : null;
            $review->makeHidden('votes');
            return $review;
        });

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
}
