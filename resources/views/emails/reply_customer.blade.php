@component('mail::message')
# 您好，{{ $contact_message->name }}

感謝您聯絡我們，以下是我們對您問題的回覆：

---

**您的問題：**

{!! nl2br(e($contact_message->description)) !!}

---

**我們的回覆：**

{!! nl2br(e($contact_message->reply_content)) !!}

如有其他問題，歡迎再次聯繫我們。

謝謝您!
我的商城客服&emsp;( ‵▽′ )
@endcomponent
