<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductsImportRequest;
use App\Models\Product;
use App\Services\Excel\ProductsImport;
use Illuminate\Support\Facades\Log;


class ProductsImportController extends Controller
{
    public function import(ProductsImportRequest $request)
    {
        try {
            $file = $request->file('file');
            $new_file_name = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs("imports", $new_file_name);
            $import = new ProductsImport();
            $import->import($path);

            $info = [
                'imported' => $import->getSuccessRowsNumber(),
                'failed' => $import->getFailedRowsNumber()
            ];
            $request->session()->flash('info', $info);
            return redirect()->back();
        } catch (\Throwable $exception) {
            Log::stack(['excel'])->info($exception->getMessage());
            abort(500);
        }
    }
}
