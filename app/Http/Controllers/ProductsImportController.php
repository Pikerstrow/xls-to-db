<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductsImportRequest;
use PhpOffice\PhpSpreadsheet\Reader\Xls;


class ProductsImportController extends Controller
{
    public function import(ProductsImportRequest $request)
    {
        try {
            dd('validation passed');
        } catch (\Throwable $exception) {
            dd($exception);
        }
    }
}
