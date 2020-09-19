<!doctype html>

<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Xls file upload</title>
    <meta name="description" content="Xls file upload">
</head>
<body>
<form method="POST" action="{{ route('upload') }}" enctype="multipart/form-data">
    @csrf
    <input type="file" name="file">
    <button type="submit">Upload</button>
</form>
@error('file')
<span style="color:red">{{ $message }}</span>
@enderror
</body>
</html>
