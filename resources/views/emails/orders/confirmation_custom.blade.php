<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đơn hàng #{{ $order->order_id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        
        .order-info {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin: 25px 0;
            border-left: 4px solid #667eea;
        }
        
        .order-info h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 20px;
            display: flex;
            align-items: center;
        }
        
        .order-info h3::before {
            content: "🛒";
            margin-right: 10px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
        }
        
        .info-value {
            color: #2c3e50;
            font-weight: 500;
        }
        
        .total-price {
            background-color: #e8f5e8;
            color: #2d5a2d;
            font-weight: bold;
            font-size: 18px;
        }
        
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-weight: 600;
            text-align: center;
            margin: 25px 0;
            transition: transform 0.2s ease;
        }
        
        .cta-button:hover {
            transform: translateY(-2px);
        }
        
        .footer {
            background-color: #2c3e50;
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .footer p {
            margin-bottom: 10px;
        }
        
        .footer .company-name {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 15px;
        }
        
        .divider {
            height: 2px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            margin: 30px 0;
            border-radius: 1px;
        }
        
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 14px;
        }
        
        .warning::before {
            content: "⚠️ ";
            font-weight: bold;
        }
        
        @media (max-width: 600px) {
            .email-container {
                margin: 0;
                box-shadow: none;
            }
            
            .content {
                padding: 20px 15px;
            }
            
            .header {
                padding: 20px 15px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .order-info {
                padding: 20px 15px;
            }
            
            .info-row {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>Xác nhận đơn hàng</h1>
            <p>Cảm ơn bạn đã đặt hàng tại {{ config('app.name') }}</p>
        </div>
        
        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Xin chào <strong>{{ $order->order_name }}</strong>,
            </div>
            
            <p>Cảm ơn bạn đã đặt hàng tại <strong>{{ config('app.name') }}</strong>. Đây là email tự động, vui lòng không trả lời trực tiếp email này.</p>
            
            <div class="warning">
                Đây là email tự động, vui lòng không trả lời trực tiếp email này.
            </div>
            
            <div class="order-info">
                <h3>Thông tin đơn hàng</h3>
                
                <div class="info-row">
                    <span class="info-label">Mã đơn hàng:</span>
                    <span class="info-value">#{{ $order->order_id }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Ngày đặt:</span>
                    <span class="info-value">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                </div>
                
                <div class="info-row total-price">
                    <span class="info-label">Tổng tiền:</span>
                    <span class="info-value">{{ number_format($order->total_price, 0, ',', '.') }} VND</span>
                </div>
            </div>
            
            <div style="text-align: center;">
                <a href="{{ route('order.show_history', $order->order_id) }}" class="cta-button">
                    Xem chi tiết đơn hàng
                </a>
            </div>
            
            <div class="divider"></div>
            
            <p style="text-align: center; font-size: 16px; color: #2c3e50;">
                Cảm ơn bạn đã tin tưởng và mua sắm cùng chúng tôi!
            </p>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="company-name">{{ config('app.name') }}</div>
            <p>Trân trọng,</p>
            <p><strong>{{ config('app.name') }}</strong></p>
            <p style="margin-top: 20px; font-size: 12px; opacity: 0.8;">
                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>

