<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;

class AdminReviewController extends Controller
{
    public function all_reviews(Request $request)
    {
        $query = Review::with(['product', 'user'])->orderBy('created_at', 'desc');

        // Tìm kiếm theo từ khóa
        if ($search = $request->input('q')) {
            $query->where('comment', 'like', "%$search%")
                ->orWhereHas('product', function($q) use ($search) {
                    $q->where('product_name', 'like', "%$search%");
                })
                ->orWhereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%");
                });
        }

        // Lọc theo trạng thái
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $reviews = $query->paginate(10)->appends($request->query());

        return view('admin.manage_reviews.all_reviews', compact('reviews'));
    }
    public function toggle(Request $request, Review $review)
    {
        $validated = $request->validate([
            'status' => 'required|in:0,1',
        ]);

        $review->update(['status' => (int) $validated['status']]);

        return back()->with('success', 'Đã cập nhật trạng thái đánh giá.');
    }

    public function destroy(Review $review)
    {
        $review->delete();

        return back()->with('success', 'Đã xoá đánh giá.');
    }
}
