<x-mail::message>
# 重設您的密碼

我們收到了您的密碼重設請求。
請點擊下方按鈕設定新密碼，連結將在 **60 分鐘**後失效。

<x-mail::button :url="$resetUrl">
重設密碼
</x-mail::button>

如果您未申請重設密碼，請忽略此信件，您的帳號不會有任何變更。

若按鈕無法點擊，請複製以下網址至瀏覽器：
{{ $resetUrl }}

</x-mail::message>
