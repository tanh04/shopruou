<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Coupon; // ✨ dùng khi fallback từ coupon_id

class InvoiceService
{
    public function generateInvoiceHtml($order_id)
    {
        $order = Order::with(['items.product', 'payment', 'user'])->findOrFail($order_id);

        // ====== TỔNG HÀNG HOÁ ======
        $itemsTotal = (int) $order->items->sum(fn($i) => (int)$i->quantity * (int)$i->price);
        $ship       = (int) ($order->shipping_fee ?? 0);

        // ====== GIẢM GIÁ: ưu tiên số lưu trên order ======
        $discount = (int)($order->discount_amount ?? 0);

        // Fallback 1: tính từ coupon_id nếu có
        if ($discount <= 0 && !empty($order->coupon_id)) {
            if ($coupon = Coupon::find($order->coupon_id)) {
                $amount  = (float)($coupon->discount_amount ?? 0);
                $percent = (float)($coupon->discount_percent ?? 0);
                if ($amount > 0) {
                    $discount = min((int)$amount, $itemsTotal);
                } elseif ($percent > 0) {
                    $discount = (int) round($itemsTotal * $percent / 100);
                }
            }
        }

        // Fallback 2: nếu bạn lưu kiểu {coupon_type: fixed|percent, coupon_value}
        if ($discount <= 0 && !empty($order->coupon_value)) {
            $type  = $order->coupon_type  ?? null;   // 'fixed' | 'percent'
            $value = (float)($order->coupon_value ?? 0);
            if ($type === 'percent') {
                $discount = (int) round($itemsTotal * $value / 100);
            } elseif ($type === 'fixed') {
                $discount = min((int)$value, $itemsTotal);
            }
        }

        // ====== THUẾ & TỔNG ====== (ưu tiên số đã lưu nếu có)
        $taxBase   = max($itemsTotal - $discount, 0);
        $tax       = (int)($order->tax_amount ?? round($taxBase * 0.05));
        $grandTotal= (int)($order->total_price ?? ($taxBase + $tax + $ship));

        // ====== HIỂN THỊ / FORMAT ======
        $fmt = fn($v) => number_format((int)$v, 0, ',', '.') . 'đ';

        $custName  = e($order->order_name ?? ($order->user->name  ?? ''));
        $custPhone = e($order->order_phone ?? '');
        $custMail  = e($order->order_email ?? ($order->user->email ?? ''));

        $recvName  = e($order->shipping_name  ?? $custName);
        $recvAddr  = e($order->order_address  ?? ($order->shipping_address ?? ''));
        $recvPhone = e($order->shipping_phone ?? $custPhone);
        $recvMail  = e($order->shipping_email ?? $custMail);
        $note      = e($order->order_note     ?? '');

        $printedAt = now()->format('d/m/Y H:i');

        // ====== DÒNG CHI TIẾT ======
        $rows = '';
        foreach ($order->items as $item) {
            $name  = e($item->product->product_name ?? ('SP#'.$item->product_id));
            $qty   = (int)($item->quantity ?? 0);
            $price = (int)($item->price ?? 0);
            $line  = $qty * $price;

            // Hiển thị mã (nếu bạn muốn)
            $code  = e($item->coupon_code ?? $order->coupon_code ?? 'không mã');

            // Nếu có discount ở từng item thì dùng; nếu không thì (tuỳ chọn) phân bổ theo tỷ lệ
            $lineDiscount = (int)($item->discount_amount ?? 0);
            if ($lineDiscount === 0 && $discount > 0 && $itemsTotal > 0) {
                // Phân bổ discount cấp order theo tỷ lệ subtotal từng dòng
                $lineDiscount = (int) round($discount * ($line / $itemsTotal));
            }

            $lineAfter = max(0, $line - $lineDiscount);

            $rows .= '
            <tr>
                <td style="vertical-align:top;">'.$name.'</td>
                <td style="text-align:center;">'.$code.'</td>
                <td class="right">'.$qty.'</td>
                <td class="right">'.$fmt($price).'</td>
                <td class="right">'.$fmt($lineAfter).'</td>
            </tr>';
        }

        // ====== PHƯƠNG THỨC & TRẠNG THÁI THANH TOÁN ======
        $methodRaw = strtolower(optional($order->payment)->payment_method ?? '');
        $methodMap = [
            'momo'  => 'MoMo',
            'vnpay' => 'VNPAY',
            'cod'   => 'Thanh toán khi nhận hàng (COD)',
            ''      => '—',
        ];
        $payMethod = $methodMap[$methodRaw] ?? (strtoupper($methodRaw) ?: '—');
        $payStatus = optional($order->payment)->payment_status ?? '—';

        return view('admin.invoices.template', compact(
            'order', 'rows', 'fmt',
            'itemsTotal',   // << THÊM
            'discount', 'ship', 'tax', 'grandTotal',
            'payMethod', 'payStatus', // << THÊM (để template dùng trực tiếp)
            'custName','custPhone','custMail',
            'recvName','recvAddr','recvPhone','recvMail',
            'note','printedAt'
        ))->render();
    }
}
