@extends('emails.layouts.master')

@section('preheader')
Your verification code is {{ $otp }}. Valid for {{ $expiryMinutes ?? 10 }} minutes.
@endsection

@section('content')
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
    <!-- Title -->
    <tr>
        <td align="center" style="padding-bottom: 12px;">
            <h1 style="font-family: 'Playfair Display', Georgia, serif; font-size: 26px; line-height: 1.3; font-weight: 700; color: #1E1E1E; margin: 0;">
                {{ $title ?? 'Security Verification' }}
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

    <!-- Greeting & Message -->
    <tr>
        <td style="font-size: 15px; line-height: 1.7; color: #4A423A; padding-bottom: 24px;">
            <p style="margin: 0 0 14px 0; font-weight: 600; color: #2A2521;">
                @if(!empty($name))
                    Dear {{ $name }},
                @else
                    Hello Beauty Enthusiast,
                @endif
            </p>
            <p style="margin: 0; color: #574E45;">
                {{ $messageBody }}
            </p>
        </td>
    </tr>

    <!-- Luxury OTP Box -->
    <tr>
        <td align="center" style="padding-bottom: 28px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background: linear-gradient(180deg, #FDFBF8 0%, #F7F2EA 100%); border: 1.5px solid #E4D5BE; border-radius: 16px; padding: 28px 20px; box-shadow: 0 4px 15px rgba(226, 201, 154, 0.15);">
                <tr>
                    <td align="center">
                        <div style="font-size: 11px; font-weight: 700; letter-spacing: 2.5px; text-transform: uppercase; color: #9A7B38; margin-bottom: 12px;">
                            ✦ ONE-TIME VERIFICATION CODE ✦
                        </div>
                        <div class="otp-code" style="font-family: 'Playfair Display', Georgia, monospace, serif; font-size: 38px; font-weight: 700; letter-spacing: 12px; color: #15803D; padding: 6px 0; text-align: center; text-indent: 12px;">
                            {{ $otp }}
                        </div>
                        <div style="margin-top: 14px; display: inline-block; background-color: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 20px; padding: 4px 14px;">
                            <span style="font-size: 12px; color: #047857; font-weight: 600;">
                                ⏱ Valid for {{ $expiryMinutes ?? 10 }} minutes
                            </span>
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- Security Advisory Note -->
    <tr>
        <td style="padding-bottom: 28px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #FBF9F5; border-left: 3px solid #15803D; border-radius: 4px; padding: 14px 18px;">
                <tr>
                    <td style="font-size: 12.5px; line-height: 1.6; color: #665D54;">
                        <strong style="color: #1E1E1E;">Security Note:</strong> Please do not forward or share this code with anyone. GlowHaus ambassadors and support staff will never request your verification code. If you did not make this request, you can safely disregard this message.
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- Action Link -->
    <tr>
        <td align="center" style="padding-bottom: 10px;">
            <a href="{{ $siteUrl ?? url('/') }}" target="_blank" style="display: inline-block; background: linear-gradient(135deg, #15803D 0%, #166534 100%); color: #FFFFFF; font-size: 13px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; text-decoration: none; padding: 14px 32px; border-radius: 50px; box-shadow: 0 4px 14px rgba(21, 128, 61, 0.28);">
                Discover GlowHaus Boutique &rarr;
            </a>
        </td>
    </tr>
</table>
@endsection
