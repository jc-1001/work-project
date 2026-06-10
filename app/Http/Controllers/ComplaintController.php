<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Review;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function store(Request $request, int $id)
    {
        $review = Review::findOrFail($id);

        $request->validate([
            'reasons'   => 'required|array|min:1',
            'reasons.*' => 'string',
        ]);

        $alreadyReported = Complaint::where('review_id', $review->id)
            ->where('user_id', auth()->id())
            ->exists();

        if ($alreadyReported) {
            return response()->json(['message' => '您已檢舉過這則評論'], 422);
        }

        Complaint::create([
            'review_id' => $review->id,
            'user_id'   => auth()->id(),
            'reasons'   => $request->reasons,
        ]);

        return response()->json(['message' => '檢舉已送出']);
    }
}
