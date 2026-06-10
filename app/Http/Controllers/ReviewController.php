<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    public function index(Request $request, $productId)
    {
        $rating = $request->input('rating');

        $reviews = Review::with(['user:id,name', 'images'])
            ->where('product_id', $productId)
            ->when($rating, fn($q) => $q->where('rating', $rating))
            ->latest()
            ->paginate(10);

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
            'reviews'    => $reviews,
            'stats'      => $stats,
        ]);
    }

    public function update(Request $request, $reviewId)
    {
        $review = Review::where('id', $reviewId)->where('user_id', auth()->id())->firstOrFail();

        $request->validate([
            'rating'     => 'required|integer|min:1|max:5',
            'content'    => 'nullable|string|max:1000',
            'keep_ids'   => 'nullable|array',
            'keep_ids.*' => 'integer',
            'images'     => 'nullable|array',
            'images.*'   => 'image|max:2048',
        ]);

        $review->update([
            'rating'  => $request->rating,
            'content' => $request->content,
        ]);

        $keepIds = $request->input('keep_ids', []);
        $review->images()->whereNotIn('id', $keepIds)->each(function ($img) {
            Storage::disk('public')->delete($img->path);
            $img->delete();
        });

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                ReviewImage::create([
                    'review_id' => $review->id,
                    'path'      => $image->store('reviews', 'public'),
                ]);
            }
        }

        return response()->json(['message' => '評論已更新', 'review' => $review->load('images')]);
    }
}
