<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\CartItems;
use App\Models\OrderItems;
use App\Models\Order;
use App\Models\Payment;
use NumberFormatter;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderDeliveredMail;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdminOrderController extends Controller
{
    public function manage_order(Request $request)
    {
        $keyword = $request->input('keyword');
        $status  = $request->input('status');

        $query = Order::with(['payment', 'user'])->orderBy('created_at', 'desc');

        if (!empty($keyword)) {
            $query->where(function($q) use ($keyword) {
                $q->where('order_id', 'LIKE', "%{$keyword}%")
                ->orWhere('order_name', 'LIKE', "%{$keyword}%")
                ->orWhere('order_phone', 'LIKE', "%{$keyword}%")
                ->orWhere('order_email', 'LIKE', "%{$keyword}%")
                ->orWhere('order_address', 'LIKE', "%{$keyword}%")
                ->orWhere('status', 'LIKE', "%{$keyword}%");
            })
            ->orWhereHas('payment', function($p) use ($keyword) {
                $p->where('payment_method', 'LIKE', "%{$keyword}%");
            });
        }


        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        $orders = $query->paginate(7)->appends($request->query());

        return view('admin.manage_order', compact('orders'));
    }

    public function order_detail($order_id){
        // Lấy đơn hàng theo ID
        $order = Order::with(['items.product', 'payment', 'user'])->findOrFail($order_id);

        return view('admin.order_detail', compact('order'));
    }

    public function updateStatus(Request $request, $order_id)
    {
        $order  = Order::with('payment')->findOrFail($order_id);
        $status = $request->input('status');

        if (!in_array($status, Order::getStatusOptions())) {
            return back()->with('error', 'Trạng thái không hợp lệ.');
        }

        // Hoàn tất -> luồng đánh dấu giao hàng
        if ($status === Order::STATUS_COMPLETED) {
            return $this->markDelivered($order_id);
        }

        // Không cho hủy nếu đã hoàn tất
        if ($status === Order::STATUS_CANCELLED && $order->status === Order::STATUS_COMPLETED) {
            return back()->with('error', 'Đơn đã hoàn tất, không thể hủy.');
        }

        // Không cho hủy nếu payment đã PAID
        if ($status === Order::STATUS_CANCELLED && $order->payment && $order->payment->isPaid()) {
            return back()->with('error', 'Đơn đã thanh toán, không thể hủy.');
        }

        $order->status = $status;
        $order->save();

        return redirect()->route('manage_order')->with('message', 'Cập nhật trạng thái thành công!');
    }


    public function markDelivered($orderId)
    {
        // Lấy đơn + user + items + payment để cập nhật đồng bộ
        $order = Order::with(['user', 'items.product', 'payment'])->findOrFail($orderId);

        // Nếu là COD, khi giao xong thì coi như đã thu tiền → Payment = PAID
        if ($order->payment && strtoupper($order->payment->payment_method) === \App\Models\Payment::METHOD_COD) {
            if ($order->payment->payment_status !== \App\Models\Payment::STATUS_PAID) {
                $order->payment->update([
                    'payment_status' => \App\Models\Payment::STATUS_PAID,
                ]);
            }
        }

        // Cập nhật trạng thái đơn
        $order->status       = Order::STATUS_COMPLETED;
        $order->delivered_at = now();
        $order->save();

        // Gửi mail thông báo đã giao (nếu có email)
        try {
            if (!empty($order->user?->email)) {
                Mail::to($order->user->email)->send(new \App\Mail\OrderDeliveredMail($order));
                Log::info("Mail xác nhận giao hàng đã gửi tới {$order->user->email} cho đơn #{$order->order_id}");
            }
        } catch (\Exception $e) {
            Log::error("Mail gửi thất bại cho đơn #{$order->order_id}: ".$e->getMessage());
            return redirect()->back()->with('error', 'Đơn đã giao nhưng gửi mail thất bại!');
        }

        return redirect()->back()->with('success', 'Đơn hàng đã giao và cập nhật thanh toán (nếu COD) thành công!');
    }


    public function order_delete($id)
    {
        $order = Order::findOrFail($id);

        // Chỉ xóa nếu đơn hàng đã bị hủy
        if ($order->status !== 'Đã hủy') {
            return redirect()->back()
                ->with('error', 'Chỉ có thể xóa đơn hàng khi trạng thái là Hủy.');
        }

        // Nếu có bảng order_items liên kết -> xóa trước
        $order->items()->delete();

        // Xóa đơn hàng
        $order->delete();

        return redirect()->route('manage_order')->with('message', 'Đơn hàng đã được xóa thành công!');

    }


    public function print_order(Request $request, $order_id, InvoiceService $invoiceService)
    {
        try {
            $html = $invoiceService->generateInvoiceHtml($order_id);
        } catch (ModelNotFoundException $e) {
            return back()->with('error', "Không tìm thấy đơn hàng #{$order_id}");
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Không thể tạo hoá đơn PDF lúc này.');
        }

        $pdf = app('dompdf.wrapper');

        // Set option MỘT lần, không tạo lại $pdf sau đó
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,       // cho phép load ảnh qua URL/asset()
            'defaultFont'          => 'DejaVu Sans', // để hiện tiếng Việt/₫
            'dpi'                  => 120,
        ]);

        $pdf->loadHTML($html)->setPaper('a4', 'portrait');

        // Cho phép xem trên trình duyệt hoặc tải về qua query ?stream=1
        if ($request->boolean('stream')) {
            return $pdf->stream("invoice-order-{$order_id}.pdf");
        }

        return $pdf->download("invoice-order-{$order_id}.pdf");
    }
}
