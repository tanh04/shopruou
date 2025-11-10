<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\Payment;

class PaymentController extends Controller
{
    /** ================= THANKS (Return URL) =================
     * Dev/test: chốt theo tham số redirect để bạn thấy kết quả ngay.
     * Prod: nên ưu tiên IPN để chốt chắc chắn.
     */
    public function thanks(Request $request, $order_id)
    {
        $order = Order::with(['items.product', 'payment'])->findOrFail($order_id);

        // Nếu đơn chưa có payment -> tạo tạm pending
        if (!$order->payment) {
            $payment = Payment::create([
                'payment_method' => null,
                'payment_status' => Payment::STATUS_PENDING,
            ]);
            $order->payment_id = $payment->payment_id;
            $order->save();
            $order->load('payment');
        }

        $payment = $order->payment;

        // --- VNPAY ---
        if ($request->has('vnp_ResponseCode')) {
            $ok = $request->get('vnp_ResponseCode') === '00';

            $payment->payment_method = Payment::METHOD_VNPAY;
            $payment->payment_status = $ok ? Payment::STATUS_PAID : Payment::STATUS_FAILED;
            $payment->save();

            $order->status = $ok ? Order::STATUS_PENDING : Order::STATUS_WAITING_PAYMENT;
            $order->save();

            session()->flash($ok ? 'success' : 'error',
                $ok ? 'Thanh toán VNPAY thành công.' : 'Thanh toán VNPAY thất bại hoặc đã hủy.'
            );

        return view('pages.payment.thanks', [
            'order'       => $order,
            'hideSidebar' => true,
            'hideSlider'  => true,
        ]);


        }

        // --- MOMO ---
        if ($request->has('resultCode')) {
            $code    = (string)$request->get('resultCode');
            $ok      = $code === '0';
            $pending = in_array($code, ['7000','7002'], true);

            $payment->payment_method = Payment::METHOD_MOMO;

            if ($ok) {
                $payment->payment_status = Payment::STATUS_PAID;
                $order->status           = Order::STATUS_PENDING;
                session()->flash('success', 'Thanh toán MoMo thành công.');
            } elseif ($pending) {
                $payment->payment_status = Payment::STATUS_PENDING;
                $order->status           = Order::STATUS_WAITING_PAYMENT;
                session()->flash('info', 'Giao dịch MoMo đang xử lý. Bạn có thể thanh toán lại sau ít phút.');
            } else {
                $payment->payment_status = Payment::STATUS_FAILED;
                $order->status           = Order::STATUS_WAITING_PAYMENT;
                session()->flash('error', 'Thanh toán MoMo thất bại hoặc đã hủy.');
            }

            $payment->save();
            $order->save();

            return view('pages.payment.thanks', [
                'order'       => $order,
                'hideSidebar' => true,
                'hideSlider'  => true,
            ]);
        }

        // Nếu không có tham số nào -> chỉ hiển thị
        return view('pages.payment.thanks', [
            'order'       => $order,
            'hideSidebar' => true,
            'hideSlider'  => true,
        ]);
    }

    /** ================= MoMo ================= */

    protected function momoCfg(): array
    {
        return [
            'endpoint'    => env('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create'),
            'partnerCode' => env('MOMO_PARTNER_CODE', 'MOMOBKUN20180529'),
            'accessKey'   => env('MOMO_ACCESS_KEY', 'klm05TvNBzhg7h7j'),
            'secretKey'   => env('MOMO_SECRET_KEY', 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa'),
            'requestType' => env('MOMO_REQUEST_TYPE', 'payWithATM'),
        ];
    }

    public function processMomoPayment($order_id, $amount)
    {
        $order = Order::with('payment')->findOrFail($order_id);

        // Ép về Chờ thanh toán trước khi đi cổng
        if ($order->status !== Order::STATUS_WAITING_PAYMENT) {
            $order->update(['status' => Order::STATUS_WAITING_PAYMENT]);
        }

        // đảm bảo payment pending
        if ($order->payment) {
            $order->payment->update([
                'payment_method' => Payment::METHOD_MOMO,
                'payment_status' => Payment::STATUS_PENDING,
            ]);
        } else {
            $p = Payment::create([
                'payment_method' => Payment::METHOD_MOMO,
                'payment_status' => Payment::STATUS_PENDING,
            ]);
            $order->payment_id = $p->payment_id;
            $order->save();
            $order->load('payment');
        }

        $cfg = $this->momoCfg();
        $redirectUrl = route('thanks', ['order_id' => $order->order_id]);
        $ipnUrl      = route('ipn.momo');

        $momoOrderId = time().'_'.$order->order_id; // để recover order_id
        $requestId   = (string) Str::uuid();
        $orderInfo   = "Thanh toán đơn hàng #{$order->order_id}";
        $amount      = (string) max(1000, (int) $amount);
        $extraData   = '';
        $requestType = $cfg['requestType'];

        $rawHash = "accessKey={$cfg['accessKey']}&amount={$amount}&extraData={$extraData}"
                 . "&ipnUrl={$ipnUrl}&orderId={$momoOrderId}&orderInfo={$orderInfo}"
                 . "&partnerCode={$cfg['partnerCode']}&redirectUrl={$redirectUrl}"
                 . "&requestId={$requestId}&requestType={$requestType}";
        $signature = hash_hmac('sha256', $rawHash, $cfg['secretKey']);

        $payload = [
            'partnerCode' => $cfg['partnerCode'],
            'partnerName' => 'YourStore',
            'storeId'     => 'Store_01',
            'requestId'   => $requestId,
            'amount'      => $amount,
            'orderId'     => $momoOrderId,
            'orderInfo'   => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl'      => $ipnUrl,
            'lang'        => 'vi',
            'extraData'   => $extraData,
            'requestType' => $requestType,
            'signature'   => $signature,
        ];

        Log::info('MoMo request payload', $payload);
        $resp = Http::asJson()->post($cfg['endpoint'], $payload);

        if (!$resp->successful()) {
            Log::error('MoMo create payment failed', ['status' => $resp->status(), 'body' => $resp->body()]);
            return back()->with('error', 'Không kết nối được MoMo.');
        }

        $json = $resp->json();
        Log::info('MoMo response', $json);

        if (!empty($json['payUrl'])) {
            return redirect()->away($json['payUrl']);
        }

        return back()->with('error', 'MoMo không trả về payUrl.');
    }

    private function momoCalcSig(array $params, string $secret): string
    {
        $raw = collect($params)->except(['signature'])->sortKeys()->map(fn($v,$k)=>$k.'='.$v)->implode('&');
        return hash_hmac('sha256', $raw, $secret);
    }

    public function momoIpn(Request $request)
    {
        $cfg = $this->momoCfg();
        $params = $request->all();
        Log::info('MoMo IPN payload', $params);

        $sigGiven = $params['signature'] ?? '';
        $sigCalc  = $this->momoCalcSig($params, $cfg['secretKey']);
        if (!hash_equals($sigCalc, $sigGiven)) {
            return response()->json(['resultCode' => 5, 'message' => 'invalid signature'], 400);
        }

        // Lấy order_id nội bộ
        $orderId = null;
        if (!empty($params['orderId']) && str_contains($params['orderId'], '_')) {
            $parts = explode('_', $params['orderId']);
            $orderId = (int) end($parts);
        } elseif (!empty($params['orderInfo']) && preg_match('/#(\d+)/', $params['orderInfo'], $m)) {
            $orderId = (int)$m[1];
        }
        if (!$orderId) return response()->json(['resultCode' => 6, 'message' => 'order not found'], 404);

        return DB::transaction(function () use ($orderId, $params) {
            $order = Order::with('payment')->lockForUpdate()->find($orderId);
            if (!$order) return response()->json(['resultCode' => 7, 'message' => 'order not found'], 404);

            if ($order->payment && $order->payment->payment_status === Payment::STATUS_PAID) {
                return response()->json(['resultCode' => 0, 'message' => 'already paid'], 200);
            }

            $paid   = (int)($params['amount'] ?? 0);
            $match  = ($paid === (int)$order->total_price);
            $code   = (string)($params['resultCode'] ?? '');
            $ok     = $code === '0' && $match;
            $pend   = in_array($code, ['7000','7002'], true);

            if ($ok) {
                $order->payment?->update([
                    'payment_method' => Payment::METHOD_MOMO,
                    'payment_status' => Payment::STATUS_PAID,
                ]);
                $order->status = Order::STATUS_PENDING;
                $order->save();
                return response()->json(['resultCode' => 0, 'message' => 'ok'], 200);
            }

            if ($pend) {
                $order->payment?->update([
                    'payment_method' => Payment::METHOD_MOMO,
                    'payment_status' => Payment::STATUS_PENDING,
                ]);
                $order->status = Order::STATUS_WAITING_PAYMENT;
                $order->save();
                return response()->json(['resultCode' => 0, 'message' => 'processing'], 200);
            }

            $order->payment?->update([
                'payment_method' => Payment::METHOD_MOMO,
                'payment_status' => Payment::STATUS_FAILED,
            ]);
            $order->status = Order::STATUS_WAITING_PAYMENT;
            $order->save();
            return response()->json(['resultCode' => 0, 'message' => 'failed'], 200);
        });
    }

    /** ================= VNPAY ================= */

   // Trong App\Http\Controllers\Customer\PaymentController

protected function vnpCfg(): array
{
    return [
        'url'     => env('VNP_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
        'tmn'     => env('VNP_TMNCODE', '1VYBIYQP'),
        'secret'  => env('VNP_HASH_SECRET', 'NOH6MBGNLQL9O9OMMFMZ2AX8NIEP50W1'),
        'version' => env('VNP_VERSION', '2.1.0'),
    ];
}

public function processVnpPayment($order_id, $amount)
{
    $order = \App\Models\Order::with('payment')->findOrFail($order_id);

    // Đưa đơn về "Chờ thanh toán" trước khi đi cổng
    if ($order->status !== \App\Models\Order::STATUS_WAITING_PAYMENT) {
        $order->update(['status' => \App\Models\Order::STATUS_WAITING_PAYMENT]);
    }

    // Đảm bảo có payment PENDING
    if ($order->payment) {
        $order->payment->update([
            'payment_method' => 'ATM', // hoặc 'VNPAY' tuỳ bạn muốn hiển thị
            'payment_status' => \App\Models\Payment::STATUS_PENDING,
        ]);
    } else {
        $p = \App\Models\Payment::create([
            'payment_method' => 'ATM',
            'payment_status' => \App\Models\Payment::STATUS_PENDING,
        ]);
        $order->payment_id = $p->payment_id;
        $order->save();
        $order->load('payment');
    }

    $cfg           = $this->vnpCfg();
    $vnp_Url       = $cfg['url'];
    $vnp_TmnCode   = $cfg['tmn'];
    $vnp_HashSecret= $cfg['secret'];
    $vnp_ReturnUrl = route('thanks', ['order_id' => $order->order_id]); // key CHUẨN: vnp_ReturnUrl

    $vnp_TxnRef    = (string) rand(100000, 999999);
    $vnp_OrderInfo = 'Thanh toán đơn hàng #' . $order->order_id;
    $vnp_OrderType = 'billpayment';
    $vnp_Amount    = (int)$amount * 100;  // VNP dùng đơn vị x100
    $vnp_Locale    = 'vn';
    $vnp_IpAddr    = request()->ip();

    $inputData = [
        'vnp_Version'    => $cfg['version'],
        'vnp_TmnCode'    => $vnp_TmnCode,
        'vnp_Amount'     => $vnp_Amount,
        'vnp_Command'    => 'pay',
        'vnp_CreateDate' => date('YmdHis'),
        'vnp_CurrCode'   => 'VND',
        'vnp_IpAddr'     => $vnp_IpAddr,
        'vnp_Locale'     => $vnp_Locale,
        'vnp_OrderInfo'  => $vnp_OrderInfo,
        'vnp_OrderType'  => $vnp_OrderType,
        'vnp_ReturnUrl'  => $vnp_ReturnUrl,
        'vnp_TxnRef'     => $vnp_TxnRef,
    ];

    // 1) Sắp xếp key
    ksort($inputData);

    // 2) Ghép chuỗi hash theo RFC3986 (rawurlencode) – KHÔNG dùng http_build_query
    $pieces = [];
    foreach ($inputData as $k => $v) {
        $pieces[] = rawurlencode($k) . '=' . rawurlencode($v);
    }
    $hashData = implode('&', $pieces);

    // 3) Tạo chữ ký
    $vnp_SecureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

    // 4) Tạo URL chuyển hướng: dùng CHÍNH chuỗi $pieces để tránh lệch encoding
    $query  = implode('&', $pieces);
    $vnp_Url = $vnp_Url . '?' . $query . '&vnp_SecureHash=' . $vnp_SecureHash;

    Log::info('VNPAY URL', ['url' => $vnp_Url, 'hashData' => $hashData]);

    return redirect()->to($vnp_Url);
}

public function vnpIpn(\Illuminate\Http\Request $request)
{
    $cfg    = $this->vnpCfg();
    $params = $request->all();
    Log::info('VNP IPN payload', $params);

    // 1) Verify chữ ký (RFC3986)
    $vnp_SecureHash = $params['vnp_SecureHash'] ?? '';
    unset($params['vnp_SecureHash'], $params['vnp_SecureHashType']);
    ksort($params);

    $pieces = [];
    foreach ($params as $k => $v) {
        $pieces[] = rawurlencode($k) . '=' . rawurlencode($v);
    }
    $hashData = implode('&', $pieces);
    $calcHash = hash_hmac('sha512', $hashData, $cfg['secret']);

    if (!hash_equals($calcHash, $vnp_SecureHash)) {
        Log::error('VNP INVALID SIGNATURE', compact('hashData','calcHash','vnp_SecureHash'));
        return response('INVALID_SIGNATURE', 400);
    }

    // 2) Lấy order_id nội bộ từ vnp_OrderInfo (ví dụ: "Thanh toán đơn hàng #123")
    $orderId = null;
    if (!empty($request->vnp_OrderInfo) && preg_match('/#(\d+)/', $request->vnp_OrderInfo, $m)) {
        $orderId = (int) $m[1];
    }
    if (!$orderId) return response('ORDER_NOT_FOUND', 404);

    // 3) Cập nhật trạng thái an toàn
    return DB::transaction(function () use ($orderId, $request) {
        $order = \App\Models\Order::with('payment')->lockForUpdate()->find($orderId);
        if (!$order) return response('ORDER_NOT_FOUND', 404);

        // Idempotent
        if ($order->payment && $order->payment->payment_status === \App\Models\Payment::STATUS_PAID) {
            return response('OK', 200);
        }

        // VNP trả về amount x100
        $paid  = (int)($request->vnp_Amount ?? 0) / 100;
        $match = ($paid === (int)$order->total_price);
        $ok    = (($request->vnp_ResponseCode ?? '') === '00') && $match;

        if ($ok) {
            // Payment -> PAID
            if ($order->payment) {
                $order->payment->update([
                    'payment_method' => 'ATM',
                    'payment_status' => \App\Models\Payment::STATUS_PAID,
                ]);
            } else {
                $p = \App\Models\Payment::create([
                    'payment_method' => 'ATM',
                    'payment_status' => \App\Models\Payment::STATUS_PAID,
                ]);
                $order->payment_id = $p->payment_id;
            }

            // Order -> Đang xử lý
            $order->status = \App\Models\Order::STATUS_PENDING;
            $order->save();

            return response('OK', 200);
        }

        // Thất bại
        if ($order->payment) {
            $order->payment->update([
                'payment_method' => 'ATM',
                'payment_status' => \App\Models\Payment::STATUS_FAILED,
            ]);
        } else {
            $p = \App\Models\Payment::create([
                'payment_method' => 'ATM',
                'payment_status' => \App\Models\Payment::STATUS_FAILED,
            ]);
            $order->payment_id = $p->payment_id;
        }

        // Order -> Chờ thanh toán
        $order->status = \App\Models\Order::STATUS_WAITING_PAYMENT;
        $order->save();

        return response('FAILED', 200);
    });
}

}
