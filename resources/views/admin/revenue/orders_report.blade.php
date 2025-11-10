<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Doanh số - Bản tĩnh</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    :root { --card: #ffffff; --muted: #6b7280; }
    body { font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji"; }
    .card { background: var(--card); border: 1px solid #e5e7eb; border-radius: 1rem; }
    .tab { @apply px-4 py-2 rounded-md text-sm font-semibold cursor-pointer; }
    .tab-active { @apply bg-white border border-emerald-500 text-emerald-600; }
    .badge { @apply inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium; }
    .stat-row { @apply flex items-center justify-between gap-3 p-3 rounded-lg border; }
  </style>
</head>
<body class="bg-gray-50 text-gray-800">
  <!-- Top bar -->
  <header class="bg-emerald-600 text-white">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
      <nav class="flex items-center gap-2 bg-emerald-700/40 p-1 rounded-md">
        <button class="tab text-white hover:bg-emerald-700/60">Tổng quan</button>
        <button class="tab tab-active bg-white text-emerald-600">Doanh số</button>
        <button class="tab text-white hover:bg-emerald-700/60">Báo cáo chốt đơn</button>
      </nav>
      <div class="flex items-center gap-2">
        <i class="fa-regular fa-calendar-days"></i>
        <span class="text-sm">24/09/2020 00:00 - 26/09/2020 23:59</span>
      </div>
    </div>
  </header>

  <main class="max-w-7xl mx-auto p-4 md:p-6">
    <!-- Title -->
    <div class="mb-4 flex items-center justify-between">
      <h1 class="text-xl font-bold tracking-tight">ĐƠN HÀNG TRONG NGÀY <span class="text-sm font-normal text-gray-500">(từ 2020-9-26 00:00 đến 2020-9-26 23:59:59)</span></h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left column: quick stats -->
      <section class="lg:col-span-2 space-y-4">
        <div class="grid sm:grid-cols-2 gap-4">
          <div class="card p-4 space-y-3">
            <div class="stat-row">
              <div class="flex items-center gap-3">
                <span class="w-8 h-8 inline-flex items-center justify-center rounded-md bg-blue-100 text-blue-600"><i class="fa-regular fa-comment"></i></span>
                <div class="text-sm text-gray-600">bình luận</div>
              </div>
              <div class="font-semibold">0</div>
            </div>
            <div class="stat-row">
              <div class="flex items-center gap-3">
                <span class="w-8 h-8 inline-flex items-center justify-center rounded-md bg-cyan-100 text-cyan-600"><i class="fa-regular fa-message"></i></span>
                <div class="text-sm text-gray-600">tin nhắn</div>
              </div>
              <div class="font-semibold">0</div>
            </div>
            <div class="stat-row">
              <div class="flex items-center gap-3">
                <span class="w-8 h-8 inline-flex items-center justify-center rounded-md bg-amber-100 text-amber-600"><i class="fa-solid fa-dollar-sign"></i></span>
                <div class="text-sm text-gray-600">vnd</div>
              </div>
              <div class="font-semibold">0</div>
            </div>
            <div class="stat-row">
              <div class="flex items-center gap-3">
                <span class="w-8 h-8 inline-flex items-center justify-center rounded-md bg-violet-100 text-violet-600"><i class="fa-solid fa-robot"></i></span>
                <div class="text-sm text-gray-600">đơn hàng tự động</div>
              </div>
              <div class="font-semibold">0</div>
            </div>
            <div class="stat-row">
              <div class="flex items-center gap-3">
                <span class="w-8 h-8 inline-flex items-center justify-center rounded-md bg-emerald-100 text-emerald-600"><i class="fa-solid fa-file-import"></i></span>
                <div class="text-sm text-gray-600">đơn nhập thủ công</div>
              </div>
              <div class="font-semibold">0</div>
            </div>
          </div>

          <div class="card p-4 space-y-3">
            <div class="stat-row">
              <div class="flex items-center gap-3">
                <span class="w-8 h-8 inline-flex items-center justify-center rounded-md bg-gray-100 text-gray-600"><i class="fa-solid fa-box"></i></span>
                <div class="text-sm text-gray-600">đơn hàng</div>
              </div>
              <div class="font-semibold">0</div>
            </div>
            <div class="stat-row">
              <div class="flex items-center gap-3">
                <span class="w-8 h-8 inline-flex items-center justify-center rounded-md bg-gray-100 text-gray-600"><i class="fa-solid fa-layer-group"></i></span>
                <div class="text-sm text-gray-600">tổng tất cả đơn hàng</div>
              </div>
              <div class="font-semibold">0</div>
            </div>
            <div class="stat-row">
              <div class="flex items-center gap-3">
                <span class="w-8 h-8 inline-flex items-center justify-center rounded-md bg-gray-100 text-gray-600"><i class="fa-solid fa-percent"></i></span>
                <div class="text-sm text-gray-600">Chiếm tổng đơn</div>
              </div>
              <div class="font-semibold">0%</div>
            </div>
          </div>
        </div>

        <!-- Charts row -->
        <div class="grid md:grid-cols-2 gap-4">
          <div class="card p-4">
            <div class="mb-2 flex items-center justify-between">
              <h2 class="font-semibold">KẾT QUẢ CHỐT ĐƠN</h2>
              <span class="text-xs text-gray-500">(từ 2020-9-24 00:00 đến 2020-9-26 23:59:59)</span>
            </div>
            <div class="relative">
              <canvas id="pieChart" height="260"></canvas>
              <div id="pieNoData" class="absolute inset-0 flex items-center justify-center text-gray-400 text-sm">No Data</div>
            </div>
          </div>
          <div class="card p-4">
            <div class="mb-2 flex items-center justify-between">
              <h2 class="font-semibold">SỐ LƯỢNG ĐƠN HÀNG</h2>
              <span class="text-xs text-gray-500">(từ 2020-9-24 00:00 đến 2020-9-26 23:59:59)</span>
            </div>
            <div class="relative">
              <canvas id="barChart" height="260"></canvas>
              <div id="barNoData" class="absolute inset-0 flex items-center justify-center text-gray-400 text-sm">No Data</div>
            </div>
          </div>
        </div>
      </section>

      <!-- Right column: order statuses -->
      <aside class="space-y-3">
        <div class="card p-4 space-y-3">
          <div class="flex items-center justify-between">
            <div class="badge bg-emerald-100 text-emerald-700"><i class="fa-solid fa-check"></i> đơn đã chốt</div>
            <span class="text-sm">đơn hàng</span>
          </div>
          <div class="flex items-center justify-between">
            <div class="badge bg-amber-100 text-amber-700"><i class="fa-regular fa-circle"></i> đơn chưa chốt</div>
            <span class="text-sm">đơn hàng</span>
          </div>
          <div class="flex items-center justify-between">
            <div class="badge bg-rose-100 text-rose-700"><i class="fa-solid fa-xmark"></i> đơn bị huỷ</div>
            <span class="text-sm">đơn hàng</span>
          </div>
          <div class="flex items-center justify-between">
            <div class="badge bg-yellow-50 text-yellow-700"><i class="fa-solid fa-ellipsis"></i> đơn khác</div>
            <span class="text-sm">đơn hàng</span>
          </div>
        </div>
      </aside>
    </div>
  </main>

  <script>
    // Dummy data (all zeros to mimic the screenshot). Update later with real numbers.
    const pieCtx = document.getElementById('pieChart');
    const barCtx = document.getElementById('barChart');

    const pieData = [0, 0, 0, 0];
    const hasPieData = pieData.some(v => v > 0);
    document.getElementById('pieNoData').style.display = hasPieData ? 'none' : 'flex';

    new Chart(pieCtx, {
      type: 'pie',
      data: {
        labels: ['Đã chốt', 'Chưa chốt', 'Bị huỷ', 'Khác'],
        datasets: [{ data: pieData }]
      },
      options: { plugins: { legend: { position: 'bottom' } } }
    });

    const barLabels = ['24/09', '25/09', '26/09'];
    const barData = [0, 0, 0];
    const hasBarData = barData.some(v => v > 0);
    document.getElementById('barNoData').style.display = hasBarData ? 'none' : 'flex';

    new Chart(barCtx, {
      type: 'bar',
      data: {
        labels: barLabels,
        datasets: [{ label: 'Số đơn hàng', data: barData }]
      },
      options: {
        scales: { y: { beginAtZero: true } },
        plugins: { legend: { display: true, position: 'top' } }
      }
    });
  </script>
</body>
</html>

@endsection