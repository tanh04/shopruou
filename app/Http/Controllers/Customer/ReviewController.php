<?php
namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;   // đảm bảo đúng model

class ReviewController extends Controller
{
    // Hiển thị sản phẩm + đánh giá
    public function show(Product $product)
    {
        // Chỉ load review đã duyệt + user để tránh N+1
        $product->load([
            'brand','category',
            'reviews' => fn($q) => $q->with('user')->where('status', 1)->latest(),
        ]);

        // Trạng thái đơn hàng được tính là đã mua
        $allowedStatuses = ['Hoàn thành']; // thêm 'Đang giao','Đã xác nhận' nếu bạn muốn

        $hasPurchased = Auth::check()
            ? Order::where('user_id', Auth::id())
                ->whereIn('status', $allowedStatuses)
                ->whereHas('items', function ($q) use ($product) {
                    $q->where('product_id', $product->product_id);
                })
                ->exists()
            : false;

        return view('pages.product.show_product', compact('product','hasPurchased'));
    }

    // Lưu đánh giá
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'comment' => 'required|string|max:2000',
            'rating'  => 'required|integer|min:1|max:5',
        ]);

        $userId = Auth::id();
        if (!$userId) {
            return redirect()->route('login');
        }

        $allowedStatuses = ['Hoàn thành']; // khớp enum tiếng Việt

        $hasPurchased = Order::where('user_id', $userId)
            ->whereIn('status', $allowedStatuses)
            ->whereHas('items', function ($q) use ($product) {
                $q->where('product_id', $product->product_id);
            })
            ->exists();

        if (!$hasPurchased) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Chỉ khách đã mua mới được đánh giá sản phẩm này.',
                    'code'    => 'NOT_VERIFIED_PURCHASE',
                ], 403);
            }
            return back()->withErrors([
                'comment' => 'Chỉ khách đã mua mới được đánh giá sản phẩm này.',
            ]);
        }

        // Không cho 1 user review 2 lần 1 sản phẩm (tuỳ chính sách)
        $existing = Review::where('product_id', $product->product_id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Bạn đã gửi đánh giá cho sản phẩm này rồi.',
                    'code'    => 'ALREADY_REVIEWED',
                ], 409);
            }
            return back()->withErrors(['comment' => 'Bạn đã đánh giá sản phẩm này rồi.']);
        }

        $review = Review::create([
            'product_id'        => $product->product_id,
            'user_id'           => $userId,
            'comment'           => $request->comment,
            'rating'            => (int) $request->rating,
            'status'            => 0,        // chờ duyệt
            'verified_purchase' => true,     // cần có cột này trong bảng reviews
        ]);

        if ($request->ajax()) {
            return response()->json([
                'message'    => 'Đã gửi đánh giá, đang chờ duyệt.',
                'user'       => Auth::user()->name,
                'rating'     => (int) $review->rating,
                'comment'    => $review->comment,
                'created_at' => $review->created_at->diffForHumans(),
                'verified'   => true,
            ], 201);
        }

        return redirect()
            ->route('product.details', ['product_id' => $product->product_id])
            ->with('success', 'Đã thêm bình luận thành công! Đánh giá của bạn đang chờ duyệt.');
    }
}
