<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Model\ProductsImport;
use App\Model\ProductsExport;

class ProductExcelController extends Controller
{
    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv']);
        Excel::import(new ProductsImport, $request->file('file'));
        return back()->with('success', 'Import thành công');
    }

    public function export()
    {
        return Excel::download(new ProductsExport, 'products.xlsx');
    }
}
