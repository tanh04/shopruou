<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Statistical;

class StatisticalController extends Controller
{
     // Mặc định trả 30 ngày gần nhất
    public function index()
    {
        $data = Statistical::query()
            ->orderByDesc('order_date')
            ->limit(30)
            ->get()
            ->sortBy('order_date') // trả theo tăng dần cho dễ vẽ chart
            ->values();

        return response()->json($data);
    }

    // Trả theo khoảng ngày ?from=2025-09-01&to=2025-09-22
    public function range(Request $request)
    {
        $request->validate([
            'from' => ['required','date'],
            'to'   => ['required','date','after_or_equal:from'],
        ]);

        $data = Statistical::betweenDates($request->from, $request->to)->get();

        return response()->json($data->values());
    }
}
