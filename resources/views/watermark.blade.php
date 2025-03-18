<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Watermark 1  App</title>
<style>
body {
font-family: sans-serif;
display: flex;
justify-content: center;
align-items: center;
min-height: 100vh;
margin: 0;
}
.container {
text-align: center;
}
input[type="file"] {
margin: 20px 0;
}
img {
max-width: 500px;
max-height: 500px;
}
</style>
</head>
<body>
<div class="container">
<h1>Watermark App</h1>
<form action="/watermark" method="post" enctype="multipart/form-data">
@csrf
<input type="file" name="image" accept="image/*">
@error('image')
<div style="color: red;">{{ $message }}</div>
@enderror<br>
<button type="submit">Apply Watermark</button>
</form>
@if(isset($watermarkedImage))
<h2>Watermarked Image:</h2>
<img src="{{ $watermarkedImage }}" alt="Watermarked Image">
@endif
</div>
</body>
</html>