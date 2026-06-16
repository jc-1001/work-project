<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @if($product)
        @php
            $ogImage = $product->image
                ? (str_starts_with($product->image, 'http') ? $product->image : asset('storage/' . $product->image))
                : '';
        @endphp
        <title>{{ $product->name }} — 我的購物網站</title>
        <meta property="og:type"        content="product" />
        <meta property="og:site_name"   content="我的購物網站" />
        <meta property="og:url"         content="{{ url()->current() }}" />
        <meta property="og:title"       content="{{ $product->name }}" />
        <meta property="og:description" content="{{ $product->description }}" />
        @if($ogImage)
        <meta property="og:image"       content="{{ $ogImage }}" />
        @endif
        <meta name="twitter:card"        content="summary_large_image" />
        <meta name="twitter:title"       content="{{ $product->name }}" />
        <meta name="twitter:description" content="{{ $product->description }}" />
        @if($ogImage)
        <meta name="twitter:image"       content="{{ $ogImage }}" />
        @endif
    @else
        <title>商品詳情 — 購物網站</title>
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app" data-page="shop-show" data-id="{{ $id }}"></div>
</body>
</html>
