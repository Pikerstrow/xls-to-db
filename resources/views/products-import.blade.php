<?php
use App\Services\Excel\ProductsImport;
use Illuminate\Support\Facades\Session;

$import_errors_file = storage_path('app/imports/errors.txt');
$info = Session::get('info');
?>
    <!doctype html>

<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Xls file upload</title>
    <meta name="description" content="Xls file upload">
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
</head>
<body>
<div class="container" style="padding-top: 40px">
    <div class="card">
        <div class="card-header">
            <span style="font-size: 1.2rem; font-weight: 700">Імпорт товарів</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('upload') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="xls-file">Виберіть файл</label>
                    <input name="file" type="file" class="form-control-file" id="xls-file">
                    @error('file')
                    <span style="color:red">{{ $message }}</span>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">
                    Завантажити
                </button>
            </form>
        </div>
    </div>

    @if(isset($info))
        <div class="card" style="margin-top: 20px">
            <div class="card-header">
                <span style="font-size: 1.2rem; font-weight: 700">Успішно імпортовано товарів: <span style="color:#007800">{{ $info['imported'] }}</span></span>
                <br>
                @if(!empty($info['failed']))
                    <span style="font-size: 1.2rem; font-weight: 700">Не вдалося імопртувати: <span style="color:#c60000">{{ $info['failed'] }}</span></span>
                @endif
            </div>
    @endif
            <div class="card-body">
                @if(file_exists($import_errors_file))
                    <h3>Причини по рядках (не вдалий імпорт)</h3>
                    @foreach(ProductsImport::getImportErrors($import_errors_file) as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                @endif
            </div>
        </div>
</div>
</body>
</html>
