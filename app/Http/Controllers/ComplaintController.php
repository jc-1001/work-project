<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'status'    => 'pending',
        ]);

        return response()->json(['message' => '檢舉已送出']);
    }

    public function adminIndex()
    {
        $complaints = Complaint::with(['review.user', 'user'])
            ->latest()
            ->paginate(15);

        return response()->json($complaints);
    }

    public function adminShow(int $id)
    {
        $complaint = Complaint::with(['review.user', 'review.images', 'review.product', 'user'])
            ->findOrFail($id);

        return response()->json($complaint);
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'action' => 'required|in:delete_review,dismiss',
        ]);

        $record = Complaint::findOrFail($id);

        if ($request->action === 'delete_review') {
            $review = $record->review;
            if ($review) {
                foreach ($review->images as $img) {
                    Storage::disk('public')->delete($img->path);
                }
                $review->delete();
            }
        } else {
            $record->update(['status' => 'dismissed']);
        }

        return response()->json(['message' => '已處理']);
    }

    public function batchUpdateStatus(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'integer',
            'action' => 'required|in:delete_review,dismiss',
        ]);

        if ($request->action === 'delete_review') {
            $complaints = Complaint::with(['review.images'])->whereIn('id', $request->ids)->get();

            foreach ($complaints as $record) {
                $review = $record->review;
                if ($review) {
                    foreach ($review->images as $img) {
                        Storage::disk('public')->delete($img->path);
                    }
                    $review->delete();
                }
            }
        } else {
            Complaint::whereIn('id', $request->ids)->update(['status' => 'dismissed']);
        }

        return response()->json(['message' => '批次處理完成']);
    }
}
