<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
</head>
<body style="font-family: Arial, sans-serif; margin: 20px; padding: 0;">
    <div style="width: 100%; max-width: 800px; margin: auto; border: 1px solid #ddd; padding: 20px;">
        <!-- Header Section -->
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="{{ public_path('email/images/logo2.png') }}" alt="Company Logo" style="width: 150px; height: auto;">
            <h2 style="margin: 5px 0;">Invoice</h2>
            {{-- <p style="margin: 0;">
                Your Company Name
                <br>
                Address Line 1
                <br>
                City, State, Zip
            </p> --}}
            <p style="margin: 0; font-weight: bold;">
                Phone: (+44) 7903073408 | Email: support@sellhub.io
            </p>
        </div>

        <table width="100%" style="margin-bottom: 30px;" cellpadding="0" cellspacing="0">
            <tr>
                <!-- Left Column (Client Details) -->
                <td width="50%" style="vertical-align: top;">
                    <h3 style="margin: 5px 0;">Bill To:</h3>
                    <p style="margin: 0;"><strong>Name:</strong> {{ $invoice->name }}</p>
                    <p style="margin: 0;"><strong>Email:</strong> {{ $invoice->email }}</p>
                    <p style="margin: 0;"><strong>Phone:</strong> {{ $invoice->phone }}</p>
                </td>
                
                <!-- Right Column (Invoice Details) -->
                <td width="50%" style="vertical-align: top; text-align: right;">
                    <p style="margin: 0;"><strong>Invoice UUID:</strong> {{ $invoice->uuid }}</p>
                    <p style="margin: 0;"><strong>Date:</strong> {{ \Carbon\Carbon::parse($invoice->created_at)->format('F j, Y') }}</p>
                    <p style="margin: 0;"><strong>Payment Status:</strong> 

                        @if ($invoice->payment_status == 1)
                            <span style="color: green;">Paid</span>
                        @elseif ($invoice->payment_status == 0)
                            <span style="color: orange;">Pending</span>
                        @else
                            <span style="color: red;">Expired</span>
                        @endif
                    </p>
                </td>
            </tr>
        </table>


        <!-- Items Table -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Product name</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Product Variant name</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Unit Price</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Quantity</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>

                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px;">{{ $invoice->product->title ?? 'N/A' }}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">{{ $invoice->variant_name ?? 'N/A' }}</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">${{ number_format($invoice->sub_total, 2) }}</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ $invoice->quantity }}</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">${{ number_format($invoice->sub_total * $invoice->quantity, 2) }}</td>
                </tr>

                <tr>
                    <td colspan="4" style="border: 1px solid #ddd; padding: 8px; text-align: right;">Sub total</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">${{ number_format($invoice->sub_total * $invoice->quantity, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="4" style="border: 1px solid #ddd; padding: 8px; text-align: right;">Discount</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">${{ number_format($invoice->discount, 2) }}</td>
                </tr>

                <tr>
                    <td colspan="4" style="border: 1px solid #ddd; padding: 8px; text-align: right; font-weight: bold;">Total</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right; font-weight: bold;">${{ number_format($invoice->total, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Payment Instructions -->
        <div>
            {{-- <h3 style="margin: 5px 0;">Payment Instructions</h3>
            <p style="margin: 5px 0;">Please make payment to Your Company Name.</p> --}}
            {{-- <p style="margin: 5px 0;">Bank Transfer: Account Number - 123456789</p> --}}
            <p style="margin: 5px 0;">Thank you for your business!</p>
        </div>

    </div>
</body>
</html>