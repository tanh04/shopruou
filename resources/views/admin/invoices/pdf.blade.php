<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>Báo cáo doanh thu & lợi nhuận</title>
  <style>
    @page { margin: 24px 28px; }
    body { font-family: DejaVu Sans, DejaVu Sans Condensed, Arial, sans-serif; font-size: 12px; color:#111; }
    h1 { font-size: 18px; text-align:center; margin:0 0 6px 0; }
    .range { text-align:center; font-style:italic; margin-bottom:14px; color:#555; }
    table { width:100%; border-collapse: collapse; }
    th, td { padding: 6px 8px; border: 1px solid #ccc; }
    th { background:#efefef; text-align:center; font-weight:700; }
    td.num { text-align:right; white-space:nowrap; }
    tr:nth-child(even) td { background:#fdfdfd; }
    .totals td { background:#f7f7f7; font-weight:700; }
    .footer { margin-top:10px; font-size:11px; color:#666; }
  </style>
</head>
<body>
  <h1>BÁO CÁO DOANH THU & LỢI NHUẬN</h1>
  <div class="range">Khoảng: {{ $from }} → {{ $to }}</div>

  <table>
    <thead>
      <tr>
        <th style="width:18%">Date</th>
        <th style="width:14%">Orders</th>
        <th style="width:14%">Quantity</th>
        <th style="width:27%">Sales (₫)</th>
        <th style="width:27%">Profit (₫)</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($rows as $r)
      <tr>
        <td>{{ $r->period }}</td>
        <td class="num">{{ number_format($r->orders) }}</td>
        <td class="num">{{ number_format($r->quantity) }}</td>
        <td class="num">{{ number_format($r->sales, 0, ',', '.') }} đ</td>
        <td class="num" @if($r->profit < 0) style="color:#9C0006;background:#FFC7CE" @endif>
          {{ number_format($r->profit, 0, ',', '.') }} đ
        </td>
      </tr>
      @endforeach

      <tr class="totals">
        <td style="text-align:left">TOTAL</td>
        <td class="num">{{ number_format($totals['orders']) }}</td>
        <td class="num">{{ number_format($totals['quantity']) }}</td>
        <td class="num">{{ number_format($totals['sales'], 0, ',', '.') }} đ</td>
        <td class="num">{{ number_format($totals['profit'], 0, ',', '.') }} đ</td>
      </tr>
    </tbody>
  </table>

  <div class="footer">
    Sinh bởi hệ thống lúc {{ now()->format('d/m/Y H:i') }}.
  </div>
</body>
</html>
