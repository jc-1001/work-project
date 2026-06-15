<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>找不到頁面</title>
    <style>
        body { margin: 0; display: flex; justify-content: center; align-items: center; min-height: 100vh; font-family: sans-serif; background: #f5f5f5; color: #333; text-align: center; }
        .code { font-size: 6rem; font-weight: 700; color: #1867c0; margin: 0; }
        .message { font-size: 1.25rem; color: #666; margin: 8px 0 24px; }
        a { display: inline-block; padding: 10px 28px; background: #1867c0; color: #fff; border-radius: 8px; text-decoration: none; font-size: 0.95rem; }
        a:hover { background: #1455a4; }
    </style>
</head>
<body>
    <div>
        <p class="code">404</p>
        <p class="message">找不到您要瀏覽的頁面</p>
        <a href="{{ url()->previous('/') }}">返回上一頁</a>
    </div>
</body>
</html>
