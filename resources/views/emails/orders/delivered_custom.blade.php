<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng đã giao thành công #{{ $order->order_id }}</title>
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
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
        
        .success-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        
        .delivery-info {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 10px;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
        }
        
        .delivery-info h3 {
            color: #155724;
            margin-bottom: 10px;
            font-size: 20px;
        }
        
        .delivery-date {
            font-size: 18px;
            font-weight: bold;
            color: #28a745;
        }
        
        .support-info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
        }
        
        .support-info h4 {
            color: #0c5460;
            margin-bottom: 10px;
        }
        
        .support-info p {
            color: #0c5460;
            margin-bottom: 8px;
        }
        
        .support-info a {
            color: #0c5460;
            text-decoration: none;
            font-weight: bold;
        }
        
        .support-info a:hover {
            text-decoration: underline;
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
            background: linear-gradient(90deg, #28a745, #20c997);
            margin: 30px 0;
            border-radius: 1px;
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
            
            .steps {
                padding: 20px 15px;
            }
            
            .step {
                flex-direction: column;
                text-align: center;
            }
            
            .step-number {
                margin-right: 0;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="success-icon">✅</div>
            <h1>Giao hàng thành công!</h1>
            <p>Đơn hàng của bạn đã được giao</p>
        </div>
        
        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Xin chào <strong>{{ $order->user->name }}</strong>,
            </div>
            
            <div class="delivery-info">
                <h3>Đơn hàng đã giao thành công</h3>
                <div class="delivery-date">
                    Đơn hàng #{{ $order->order_id }} đã được giao thành công vào ngày {{ $order->delivered_at->format('d/m/Y') }}
                </div>
            </div>
            
            <div class="support-info">
                <h4>Hỗ trợ khách hàng</h4>
                <p>Nếu có vấn đề gì với đơn hàng, vui lòng liên hệ:</p>
                <p>
                    📧 Email: <a href="mailto:support@example.com">support@example.com</a><br>
                    📞 Hotline: <strong>0943785681</strong>
                </p>
            </div>
            
            <div class="divider"></div>
            
            <p style="text-align: center; font-size: 16px; color: #2c3e50;">
                Cảm ơn bạn đã mua sắm tại <strong>{{ config('app.name') }}</strong>!
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
