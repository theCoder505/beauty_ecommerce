@extends('emails.layouts.master')

@section('preheader')
Response to your inquiry: {{ $contact->subject }}
@endsection

@section('content')
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
    <!-- Title -->
    <tr>
        <td align="center" style="padding-bottom: 12px;">
            <h1 style="font-family: 'Playfair Display', Georgia, serif; font-size: 24px; line-height: 1.3; font-weight: 700; color: #1E1E1E; margin: 0;">
                Response to Your Inquiry
            </h1>
        </td>
    </tr>

    <!-- Decorative Accent Bar -->
    <tr>
        <td align="center" style="padding-bottom: 24px;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="width: 30px; height: 1px; background-color: #E2C99A;"></td>
                    <td style="padding: 0 10px; font-size: 13px; color: #C5A059;">✦</td>
                    <td style="width: 30px; height: 1px; background-color: #E2C99A;"></td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- Greeting -->
    <tr>
        <td style="font-size: 15px; line-height: 1.7; color: #4A423A; padding-bottom: 16px;">
            <p style="margin: 0 0 14px 0; font-weight: 600; color: #2A2521;">
                Hello {{ $contact->name }},
            </p>
            <p style="margin: 0; color: #574E45;">
                Thank you for contacting {{ $siteName }}. Our team has reviewed your message regarding <strong>"{{ $contact->subject }}"</strong>.
            </p>
        </td>
    </tr>

    <!-- Admin Reply Box -->
    <tr>
        <td style="padding-bottom: 24px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #FDFBF8; border-left: 4px solid #15803D; border-radius: 8px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                <tr>
                    <td>
                        <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #15803D; margin-bottom: 8px;">
                            ✦ Support Team Reply
                        </div>
                        <div style="font-size: 14px; line-height: 1.7; color: #2A2521; white-space: pre-line;">
{{ $reply }}
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- Original Message Quote -->
    <tr>
        <td style="padding-bottom: 24px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 8px; padding: 16px;">
                <tr>
                    <td>
                        <div style="font-size: 12px; font-weight: 600; color: #6B7280; margin-bottom: 6px;">
                            Your Original Message (Sent on {{ $contact->created_at->format('M d, Y h:i A') }}):
                        </div>
                        <div style="font-size: 13px; line-height: 1.6; color: #4B5563; font-style: italic;">
                            "{{ $contact->message }}"
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- Footer Note -->
    <tr>
        <td style="font-size: 13px; line-height: 1.6; color: #6B7280; text-align: center; border-top: 1px solid #F3F4F6; padding-top: 16px;">
            If you have any further questions, feel free to reply directly or visit our contact page.
        </td>
    </tr>
</table>
@endsection
