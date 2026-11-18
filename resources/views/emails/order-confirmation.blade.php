@extends('emails.layouts.master')

@section('preheader')
Order #{{ $order->order_number }} confirmed! Thank you for choosing {{ $siteName ?? 'GlowHaus' }}.
@endsection

@section('content')
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
    <!-- Title -->
    <tr>
        <td align="center" style="padding-bottom: 8px;">
            <div style="font-size: 11px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #15803D; margin-bottom: 8px;">
                ✦ THANK YOU FOR YOUR ORDER ✦
            </div>
            <h1 style="font-family: 'Playfair Display', Georgia, serif; font-size: 26px; line-height: 1.3; font-weight: 700; color: #1E1E1E; margin: 0;">
                Your Ritual Begins Here
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

    <!-- Greeting & Order Number -->
    <tr>
        <td style="font-size: 15px; line-height: 1.7; color: #4A423A; padding-bottom: 24px;">
            <p style="margin: 0 0 12px 0; font-weight: 600; color: #2A2521;">
                Dear {{ $order->shipping_name ?? 'Valued Customer' }},
            </p>
            <p style="margin: 0; color: #574E45;">
                We are delighted to confirm your order <strong>#{{ $order->order_number }}</strong>. Our atelier is meticulously preparing and hand-packaging your beauty selections.
            </p>
        </td>
    </tr>

    <!-- Order Items Table -->
    <tr>
        <td style="padding-bottom: 24px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border: 1px solid #ECE4D8; border-radius: 12px; overflow: hidden;">
                <tr style="background-color: #FAF7F2; border-bottom: 1px solid #ECE4D8;">
                    <th align="left" style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #78716C;">Item</th>
                    <th align="center" style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #78716C;">Qty</th>
                    <th align="right" style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #78716C;">Total</th>
                </tr>
                @foreach($order->items as $item)
                <tr style="border-bottom: 1px solid #F3EDE4;">
                    <td style="padding: 14px 16px;">
                        <div style="font-weight: 600; font-size: 14px; color: #1E1E1E;">{{ $item->product_name }}</div>
                        @if(!empty($item->options_summary))
                            <div style="font-size: 11px; font-weight: 600; color: #15803d; margin-top: 2px;">{{ $item->options_summary }}</div>
                        @endif
                        <div style="font-size: 12px; color: #78716C; margin-top: 2px;">{{ $currencySymbol }}{{ number_format((float) ($item->unit_price ?? 0), 2) }} each</div>
                    </td>
                    <td align="center" style="padding: 14px 16px; font-size: 13px; color: #4A423A;">
                        &times; {{ $item->quantity }}
                    </td>
                    <td align="right" style="padding: 14px 16px; font-weight: 600; font-size: 14px; color: #1E1E1E;">
                        {{ $currencySymbol }}{{ number_format((float) ($item->total ?? $item->subtotal ?? 0), 2) }}
                    </td>
                </tr>
                @endforeach
                <!-- Order Summary Breakdown -->
                <tr style="background-color: #FAF7F2;">
                    <td colspan="2" align="right" style="padding: 10px 16px 4px 16px; font-size: 12px; color: #78716C;">Subtotal:</td>
                    <td align="right" style="padding: 10px 16px 4px 16px; font-size: 13px; color: #1E1E1E; font-weight: 600;">{{ $currencySymbol }}{{ number_format((float) ($order->subtotal ?? 0), 2) }}</td>
                </tr>
                @if(($order->discount ?? $order->discount_amount ?? 0) > 0)
                <tr style="background-color: #FAF7F2;">
                    <td colspan="2" align="right" style="padding: 4px 16px; font-size: 12px; color: #15803D;">Discount:</td>
                    <td align="right" style="padding: 4px 16px; font-size: 13px; color: #15803D; font-weight: 600;">-{{ $currencySymbol }}{{ number_format((float) ($order->discount ?? $order->discount_amount), 2) }}</td>
                </tr>
                @endif
                <tr style="background-color: #FAF7F2;">
                    <td colspan="2" align="right" style="padding: 4px 16px; font-size: 12px; color: #78716C;">Shipping:</td>
                    <td align="right" style="padding: 4px 16px; font-size: 13px; color: #1E1E1E; font-weight: 600;">
                        {{ ($order->shipping_cost ?? 0) > 0 ? $currencySymbol . number_format((float) $order->shipping_cost, 2) : 'Free' }}
                    </td>
                </tr>
                <tr style="background-color: #FAF7F2; border-top: 1px solid #ECE4D8;">
                    <td colspan="2" align="right" style="padding: 14px 16px; font-size: 14px; font-weight: 700; color: #1E1E1E;">Grand Total:</td>
                    <td align="right" style="padding: 14px 16px; font-size: 16px; font-weight: 700; color: #15803D;">{{ $currencySymbol }}{{ number_format((float) ($order->total ?? $order->total_amount ?? 0), 2) }}</td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- Shipping Details -->
    <tr>
        <td style="padding-bottom: 24px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #FAF7F2; border-radius: 12px; border: 1px solid #ECE4D8; padding: 18px 20px;">
                <tr>
                    <td>
                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #78716C; margin-bottom: 8px;">Shipping Address</div>
                        <div style="font-size: 13px; line-height: 1.6; color: #2A2521;">
                            <strong>{{ $order->shipping_name }}</strong><br>
                            {{ $order->shipping_address }}<br>
                            {{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_zip }}<br>
                            {{ $order->shipping_country }}<br>
                            Phone: {{ $order->shipping_phone }}
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- View Order CTA -->
    <tr>
        <td align="center" style="padding-bottom: 10px;">
            <a href="{{ !empty($order->order_number ?? $order->id) ? route('user.orders.show', $order) : route('user.orders') }}" target="_blank" style="display: inline-block; background: linear-gradient(135deg, #15803D 0%, #166534 100%); color: #FFFFFF; font-size: 13px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; text-decoration: none; padding: 14px 32px; border-radius: 50px; box-shadow: 0 4px 14px rgba(21, 128, 61, 0.28);">
                View Order Details &rarr;
            </a>
        </td>
    </tr>
</table>
@endsection
