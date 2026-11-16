<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ContactReplyMail;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $query = ContactMessage::with(['user', 'repliedByAdmin']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $messages = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total'   => ContactMessage::count(),
            'pending' => ContactMessage::where('status', 'pending')->count(),
            'replied' => ContactMessage::where('status', 'replied')->count(),
        ];

        return view('admin.contact-messages.index', compact('messages', 'stats'));
    }

    public function show(ContactMessage $contactMessage): View
    {
        $contactMessage->load(['user', 'repliedByAdmin']);
        return view('admin.contact-messages.show', compact('contactMessage'));
    }

    public function reply(Request $request, ContactMessage $contactMessage): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'reply' => ['required', 'string', 'min:5', 'max:5000'],
        ]);

        // Send email to visitor
        Mail::to($contactMessage->email)->send(new ContactReplyMail($contactMessage, $data['reply']));

        // Save reply in database
        $contactMessage->update([
            'admin_reply' => $data['reply'],
            'replied_by'  => auth('admin')->id(),
            'replied_at'  => now(),
            'status'      => 'replied',
        ]);

        $message = "Reply successfully sent to {$contactMessage->email} and recorded.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->delete();
        return redirect()->route('admin.contact-messages.index')->with('success', 'Contact message deleted.');
    }
}
