<?php

namespace App\Http\Controllers;

use App\Models\Review;
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
}
