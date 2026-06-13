<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use App\Mail\ReplyCustomer;
use Illuminate\Support\Facades\Mail;

class ContactMessageController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'email'               => 'required|string',
            'category'            => 'required|in:product,order,other',
            'description'         => 'required|string|max:2000',
        ]);

        $contactMessage = ContactMessage::create($validated);

        return response()->json(['contact_message' => $contactMessage], 201);
    }

    public function adminIndex()
    {
        $contactMessages = ContactMessage::orderBy('created_at', 'desc')->get();

        return response()->json([
            'contact_messages' => $contactMessages,
        ]);
    }

    public function adminShow(int $id)
    {
        $contactMessage = ContactMessage::findOrFail($id);

        return response()->json([
            'contact_message' => $contactMessage,
        ]);
    }

    public function updateStatus(Request $request, ContactMessage $contactMessage)
    {
        $request->validate([
            'status' => 'required|in:pending,replied',
        ]);

        $contactMessage->update([
            'status'      => $request->status,
            'replied_at'  => $request->status === 'replied' ? now() : null,
        ]);

        return response()->json([
            'contact_message' => $contactMessage->fresh(),
        ]);
    }

    public function adminReplyStore(Request $request, ContactMessage $contactMessage)
    {
        $request->validate([
            'reply_content' => 'required|string|max:2000',
        ]);

        $contactMessage->update([
            'reply_content' => $request->reply_content,
            'status'        => 'replied',
            'replied_at'    => now(),
        ]);

        Mail::to($contactMessage->email)->send(new ReplyCustomer($contactMessage->fresh()));

        return response()->json([
            'contact_message' => $contactMessage->fresh(),
        ]);
    }

    public function batchUpdateStatus(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array',
            'ids.*'  => 'integer|exists:contact_messages,id',
            'status' => 'required|in:pending,replied',
        ]);

        ContactMessage::whereIn('id', $request->ids)->update([
            'status'     => $request->status,
            'replied_at' => $request->status === 'replied' ? now() : null,
        ]);

        return response()->json(['message' => '更新成功']);
    }
}
