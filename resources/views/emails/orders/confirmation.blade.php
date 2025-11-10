<!-- <x-mail::message>
# Introduction

The body of your message.

<x-mail::button :url="''">
Button Text
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message> -->

@component('mail::message')
# Xác nhận đơn hàng #{{ $order->order_id }}

Xin chào **{{ $order->order_name }}**,

Cảm ơn bạn đã đặt hàng tại **{{ config('app.name') }}**.  
Đây là email tự động, vui lòng **không trả lời trực tiếp** email này.

---

### 🛒 Thông tin đơn hàng
- **Mã đơn hàng:** {{ $order->order_id }}  
- **Ngày đặt:** {{ $order->created_at->format('d/m/Y H:i') }}  
- **Tổng tiền:** {{ number_format($order->total_price, 0, ',', '.') }} VND  

@component('mail::button', ['url' => route('order.show_history', $order->order_id)])
Xem chi tiết đơn hàng
@endcomponent

---

Cảm ơn bạn đã tin tưởng và mua sắm cùng chúng tôi!  

Trân trọng,  
**{{ config('app.name') }}**

@endcomponent
