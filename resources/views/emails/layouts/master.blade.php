<!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ $subject ?? $siteName ?? 'GlowHaus' }}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&family=Inter:wght@300;400;500;600;700&display=swap');

        /* Client specific resets */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        table { border-collapse: collapse !important; }
        body { margin: 0 !important; padding: 0 !important; width: 100% !important; background-color: #F8F5F0; }

        /* Mobile layout */
        @media only screen and (max-width: 620px) {
            .email-container { width: 100% !important; max-width: 100% !important; border-radius: 0 !important; }
            .mobile-padding { padding-left: 20px !important; padding-right: 20px !important; }
            .mobile-stack { display: block !important; width: 100% !important; max-width: 100% !important; direction: ltr !important; }
            .mobile-spacer { height: 16px !important; }
            .otp-code { font-size: 28px !important; letter-spacing: 8px !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #F8F5F0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; color: #2A2521;">

    {{-- Preheader hidden snippet --}}
    <div style="display: none; font-size: 1px; color: #F8F5F0; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden;">
        @yield('preheader', 'Luxury beauty & bespoke fragrance curated for your ritual.')
        &#847; &zwnj; &nbsp; &#8199; &shy; &#847; &zwnj; &nbsp; &#8199; &shy; &#847; &zwnj; &nbsp; &#8199; &shy;
    </div>

    <!-- Outer Wrapper -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #F8F5F0; min-height: 100vh;">
        <tr>
            <td align="center" style="padding: 40px 15px 40px 15px;">

                <!-- Container (Max 600px) -->
                <table role="presentation" class="email-container" width="600" cellpadding="0" cellspacing="0" border="0" style="width: 100%; max-width: 600px; background-color: #FFFFFF; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 35px rgba(42, 37, 33, 0.06); border: 1px solid #ECE4D8;">

                    <!-- Brand Top Bar -->
                    <tr>
                        <td align="center" style="background: linear-gradient(135deg, #15803D 0%, #0F5128 100%); padding: 8px 20px;">
                            <p style="margin: 0; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; color: #D1FAE5; font-weight: 500;">
                                ✦ Complimentary Standard Shipping on Orders Over ৳2,000 ✦
                            </p>
                        </td>
                    </tr>

                    <!-- Luxury Header -->
                    <tr>
                        <td align="center" style="padding: 36px 40px 24px 40px; background-color: #FAF6F0; border-bottom: 1px solid #EFE8DE;" class="mobile-padding">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $siteUrl ?? url('/') }}" target="_blank" style="text-decoration: none;">
                                            <span style="font-family: 'Playfair Display', Georgia, serif; font-size: 30px; font-weight: 700; letter-spacing: 3px; color: #15803D; text-transform: uppercase; display: inline-block;">
                                                ✦ {{ $siteName ?? 'GlowHaus' }}
                                            </span>
                                        </a>
                                        <div style="font-size: 11px; font-weight: 500; letter-spacing: 3px; text-transform: uppercase; color: #8C827A; margin-top: 6px;">
                                            Haute Parfumerie &bull; Clean Beauty &bull; Rituals
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 40px 40px 30px 40px;" class="mobile-padding">
                            @yield('content')
                        </td>
                    </tr>

                    <!-- Beauty Brand Pillars -->
                    <tr>
                        <td style="padding: 0 40px 35px 40px;" class="mobile-padding">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #FAF7F2; border-radius: 14px; border: 1px solid #ECE4D8; padding: 20px;">
                                <tr>
                                    <td>
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td class="mobile-stack" align="center" valign="top" style="padding: 10px 8px; width: 33.33%;">
                                                    <div style="font-size: 20px; line-height: 1; margin-bottom: 6px;">🌿</div>
                                                    <div style="font-size: 12px; font-weight: 600; color: #1E1E1E; text-transform: uppercase; letter-spacing: 0.5px;">Clean & Vegan</div>
                                                    <div style="font-size: 11px; color: #78716C; margin-top: 3px; line-height: 1.4;">Pure botanical actives</div>
                                                </td>
                                                <td class="mobile-stack" align="center" valign="top" style="padding: 10px 8px; width: 33.33%;">
                                                    <div style="font-size: 20px; line-height: 1; margin-bottom: 6px;">✦</div>
                                                    <div style="font-size: 12px; font-weight: 600; color: #1E1E1E; text-transform: uppercase; letter-spacing: 0.5px;">Artisanal Notes</div>
                                                    <div style="font-size: 11px; color: #78716C; margin-top: 3px; line-height: 1.4;">Handcrafted in Paris</div>
                                                </td>
                                                <td class="mobile-stack" align="center" valign="top" style="padding: 10px 8px; width: 33.33%;">
                                                    <div style="font-size: 20px; line-height: 1; margin-bottom: 6px;">📦</div>
                                                    <div style="font-size: 12px; font-weight: 600; color: #1E1E1E; text-transform: uppercase; letter-spacing: 0.5px;">Luxury Packaging</div>
                                                    <div style="font-size: 11px; color: #78716C; margin-top: 3px; line-height: 1.4;">Signature gift box</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Luxury Footer -->
                    <tr>
                        <td align="center" style="background-color: #211B17; color: #D6CEC5; padding: 36px 40px; border-top: 1px solid #332B25;" class="mobile-padding">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center">
                                        <span style="font-family: 'Playfair Display', Georgia, serif; font-size: 20px; font-weight: 700; letter-spacing: 2px; color: #FFFFFF; text-transform: uppercase;">
                                            ✦ {{ $siteName ?? 'GlowHaus' }}
                                        </span>
                                        <p style="margin: 10px 0 16px 0; font-size: 12px; color: #A89E94; line-height: 1.6; max-width: 440px;">
                                            Elevating your daily beauty ritual with fine fragrances and dermatologically conscious botanicals.
                                        </p>

                                        <!-- Boutique Links -->
                                        <div style="margin: 16px 0 20px 0;">
                                            <a href="{{ $siteUrl ?? url('/') }}" target="_blank" style="color: #4ADE80; font-size: 12px; text-decoration: none; font-weight: 600; margin: 0 10px;">The Boutique</a>
                                            <span style="color: #574E45;">&bull;</span>
                                            <a href="{{ ($siteUrl ?? url('/')) . '/#products' }}" target="_blank" style="color: #D6CEC5; font-size: 12px; text-decoration: none; margin: 0 10px;">Fragrances</a>
                                            <span style="color: #574E45;">&bull;</span>
                                            <a href="{{ ($siteUrl ?? url('/')) . '/#reviews' }}" target="_blank" style="color: #D6CEC5; font-size: 12px; text-decoration: none; margin: 0 10px;">Customer Reviews</a>
                                        </div>

                                        <div style="border-top: 1px solid #38302A; margin: 20px 0; width: 100%;"></div>

                                        <!-- Concierge Support -->
                                        <p style="margin: 0 0 8px 0; font-size: 12px; color: #A89E94;">
                                            Need concierge assistance? Email us at <a href="mailto:{{ $contactEmail ?? 'hello@glowhaus.com' }}" style="color: #4ADE80; text-decoration: none; font-weight: 500;">{{ $contactEmail ?? 'hello@glowhaus.com' }}</a>
                                            @if(!empty($contactPhone))
                                                &bull; {{ $contactPhone }}
                                            @endif
                                        </p>

                                        <!-- Legal / Copyright -->
                                        <p style="margin: 12px 0 0 0; font-size: 11px; color: #786E64; line-height: 1.5;">
                                            &copy; {{ date('Y') }} {{ $siteName ?? 'GlowHaus Beauty' }}. All rights reserved.<br>
                                            This is an automated luxury service communication regarding your account.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>
                <!-- /Container -->

            </td>
        </tr>
    </table>
    <!-- /Outer Wrapper -->

</body>
</html>
