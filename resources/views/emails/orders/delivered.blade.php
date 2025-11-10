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
# Xin chào {{ $order->user->name }},

Đơn hàng của bạn **#{{ $order->order_id }}** đã được giao thành công vào ngày **{{ $order->delivered_at->format('d/m/Y') }}**.

**Chi tiết đơn hàng:**  
@foreach($order->items as $item)
- {{ $item->product->name }} x {{ $item->quantity }} : {{ number_format($item->total_price) }}₫
@endforeach

**Tổng tiền:** {{ number_format($order->total_price) }}₫  
**Địa chỉ giao hàng:** {{ $order->order_address }}

Nếu có vấn đề gì với đơn hàng, vui lòng liên hệ **support@example.com** hoặc hotline 0943785681để được hỗ trợ.

Cảm ơn bạn đã mua sắm tại **{{ config('app.name') }}**.

Trân trọng,  
{{ config('app.name') }}
@endcomponent
