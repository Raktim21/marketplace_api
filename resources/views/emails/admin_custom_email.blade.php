{{-- <!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ $subject }}</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: Arial, sans-serif;
                background-color: #f3f4f6;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1rem;
            }

            .email-container {
                width: 100%;
                max-width: 100%;
                background-color: #131d2c;
                color: white;
                border-radius: 0.5rem;
                overflow: hidden;
            }

            .logo-section {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1.5rem;
                border-bottom: 1px solid #374151;
                gap: 2rem;
                width: 100%;
            }

            .logo {
                width: 2.5rem;
                height: 2.5rem;
                object-fit: contain;
                border-radius: 0.25rem;
            }

            .content {
                padding: 2rem;
                max-width: 800px;
                margin: 0 auto;
                width: 100%;
            }

            h1 {
                font-size: 1.75rem;
                font-weight: bold;
                margin-bottom: 1.5rem;
                text-align: center;
            }

            p {
                margin-bottom: 1.25rem;
                line-height: 1.6;
                font-size: 1rem;
            }

            .cta-button {
                text-align: center;
                margin: 2rem 0;
            }

            .button {
                background-color: white;
                color: black;
                padding: 0.75rem 2rem;
                border-radius: 9999px;
                font-weight: 600;
                text-decoration: none;
                display: inline-block;
                transition: background-color 0.3s;
                font-size: 1rem;
            }

            .button:hover {
                background-color: #e5e7eb;
            }

            .footer {
                background-color: #111827;
                padding: 1rem;
                text-align: center;
                width: 100%;
                border-top: 1px solid #374151;
            }

            .footer-links {
                display: flex;
                justify-content: center;
                gap: 2rem;
                align-items: center;
                padding: 0.5rem;
            }

            .footer-link {
                color: #9ca3af;
                text-decoration: none;
                font-size: 0.875rem;
                transition: color 0.3s;
                padding: 0.25rem 0.5rem;
            }

            .footer-link:hover {
                color: white;
            }

            @media (max-width: 640px) {
                .logo {
                    width: 2rem;
                    height: 2rem;
                }

                .content {
                    padding: 1.5rem;
                }

                .footer-links {
                    gap: 1rem;
                }
            }

        </style>
    </head>

    <body>
        <div class="email-container">
            <!-- Top Logo Section -->
            <div class="logo-section">
                <a href="https://sellhub.io">
                    <img src="{{ asset('email/images/favico.png') }}" class="logo">
                </a>
            </div>


            <!-- Email Content -->
            <div class="content">
                {!! $data !!}
            </div>

            <!-- Bottom Logo Section -->
            <div class="logo-section" style="border-bottom: none;">
                <a href="https://sellhub.io">
                    <img src="{{ asset('email/images/favico.png') }}" class="logo">
                </a>
            </div>

            <!-- Footer Links -->
            <div class="footer">
                <div class="footer-links">
                    <a href="https://sellhub.io/tos" class="footer-link">Terms of Service</a>
                    <a href="https://sellhub.io/contact" class="footer-link">Contact Us</a>
                </div>
            </div>
        </div>
    </body>

</html> --}}



<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $subject }}</title>
    <style type="text/css">
        a {
            text-decoration: none;
            outline: none;
        }

        @media (max-width: 649px) {
            .o_col-full {
                max-width: 100% !important;
            }

            .o_col-half {
                max-width: 50% !important;
            }

            .o_hide-lg {
                display: inline-block !important;
                font-size: inherit !important;
                max-height: none !important;
                line-height: inherit !important;
                overflow: visible !important;
                width: auto !important;
                visibility: visible !important;
            }

            .o_hide-xs,
            .o_hide-xs.o_col_i {
                display: none !important;
                font-size: 0 !important;
                max-height: 0 !important;
                width: 0 !important;
                line-height: 0 !important;
                overflow: hidden !important;
                visibility: hidden !important;
                height: 0 !important;
            }

            .o_xs-center {
                text-align: center !important;
            }

            .o_xs-left {
                text-align: left !important;
            }

            .o_xs-right {
                text-align: left !important;
            }

            table.o_xs-left {
                margin-left: 0 !important;
                margin-right: auto !important;
                float: none !important;
            }

            table.o_xs-right {
                margin-left: auto !important;
                margin-right: 0 !important;
                float: none !important;
            }

            table.o_xs-center {
                margin-left: auto !important;
                margin-right: auto !important;
                float: none !important;
            }

            h1.o_heading {
                font-size: 32px !important;
                line-height: 41px !important;
            }

            h2.o_heading {
                font-size: 26px !important;
                line-height: 37px !important;
            }

            h3.o_heading {
                font-size: 20px !important;
                line-height: 30px !important;
            }

            .o_xs-py-md {
                padding-top: 24px !important;
                padding-bottom: 24px !important;
            }

            .o_xs-pt-xs {
                padding-top: 8px !important;
            }

            .o_xs-pb-xs {
                padding-bottom: 8px !important;
            }
        }

        @media screen {
            @font-face {
                font-family: 'Roboto';
                font-style: normal;
                font-weight: 400;
                src: local("Roboto"), local("Roboto-Regular"), url(https://fonts.gstatic.com/s/roboto/v18/KFOmCnqEu92Fr1Mu7GxKOzY.woff2) format("woff2");
                unicode-range: U+0100-024F, U+0259, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF;
            }

            @font-face {
                font-family: 'Roboto';
                font-style: normal;
                font-weight: 400;
                src: local("Roboto"), local("Roboto-Regular"), url(https://fonts.gstatic.com/s/roboto/v18/KFOmCnqEu92Fr1Mu4mxK.woff2) format("woff2");
                unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
            }

            @font-face {
                font-family: 'Roboto';
                font-style: normal;
                font-weight: 700;
                src: local("Roboto Bold"), local("Roboto-Bold"), url(https://fonts.gstatic.com/s/roboto/v18/KFOlCnqEu92Fr1MmWUlfChc4EsA.woff2) format("woff2");
                unicode-range: U+0100-024F, U+0259, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF;
            }

            @font-face {
                font-family: 'Roboto';
                font-style: normal;
                font-weight: 700;
                src: local("Roboto Bold"), local("Roboto-Bold"), url(https://fonts.gstatic.com/s/roboto/v18/KFOlCnqEu92Fr1MmWUlfBBc4.woff2) format("woff2");
                unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
            }

            .o_sans,
            .o_heading {
                font-family: "Roboto", sans-serif !important;
            }

            .o_heading,
            strong,
            b {
                font-weight: 700 !important;
            }

            a[x-apple-data-detectors] {
                color: inherit !important;
                text-decoration: none !important;
            }
        }

    </style>
</head>

<body class="o_body o_bg-white"
    style="width: 100%;margin: 0px;padding: 0px;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;background-color: #ffffff;">
    <!-- preview-text -->
    <table width="100%" cellspacing="0" cellpadding="0" border="0" role="presentation">
        <tbody>
            <tr>
                <td class="o_hide" align="center"
                    style="display: none;font-size: 0;max-height: 0;width: 0;line-height: 0;overflow: hidden;mso-hide: all;visibility: hidden;">
                    {{ $subject }}
                </td>
            </tr>
        </tbody>
    </table>
    <!-- header -->
    <table width="100%" cellspacing="0" cellpadding="0" border="0" role="presentation">
        <tbody>
            <tr>
                <td class="o_bg-dark o_px-md o_py-md o_sans o_text" align="center" style="font-family: Helvetica, Arial, sans-serif;margin-top: 0px;margin-bottom: 0px;font-size: 16px;line-height: 24px;background-color: #242b3d;padding-left: 24px;padding-right: 24px;padding-top: 24px;padding-bottom: 24px;">
                    <p style="margin-top: 0px;margin-bottom: 0px;">
                        <a class="o_text-white" href="https://sellhub.io/" style="text-decoration: none;outline: none;color: #ffffff;">
                            <img src="{{ asset('email/images/logo.png') }}" width="200" height="60" alt="Sellhub"  style="-ms-interpolation-mode: bicubic;vertical-align: middle;border: 0;line-height: 100%;height: auto;outline: none;text-decoration: none;">
                        </a>
                    </p>
                </td>
            </tr>
        </tbody>
    </table>
    <!-- hero-dark-button -->
    <table width="100%" cellspacing="0" cellpadding="0" border="0" role="presentation">
        <tbody>
            <tr>
                <td class="o_bg-dark o_px-md o_py-xl o_xs-py-md" align="center" style="background-color: #242b3d;padding-left: 24px;padding-right: 24px;padding-top: 64px;padding-bottom: 64px;">
                    <!--[if mso]><table width="584" cellspacing="0" cellpadding="0" border="0" role="presentation"><tbody><tr><td align="center"><![endif]-->
                    <div class="o_col-6s o_sans o_text-md o_text-white o_center" style="font-family: Helvetica, Arial, sans-serif;margin-top: 0px;margin-bottom: 0px;font-size: 19px;line-height: 28px;max-width: 584px;color: #ffffff;text-align: center;">
                        <h2 class="o_heading o_mb-xxs" style="font-family: Helvetica, Arial, sans-serif;font-weight: bold;margin-top: 0px;margin-bottom: 4px;font-size: 30px;line-height: 39px;">
                            {{ $subject }}
                        </h2>
                        <p class="o_mb-md" style="margin-top: 0px;margin-bottom: 24px;">
                            {!! $data !!}
                        </p>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    <!-- footer -->
    <table width="100%" cellspacing="0" cellpadding="0" border="0" role="presentation">
        <tbody>
            <tr>
                <td class="o_bg-dark o_px-md o_py-lg" align="center" style="background-color: #242b3d;padding-left: 24px;padding-right: 24px;padding-top: 32px;padding-bottom: 32px;">
                    <div class="o_col-6s o_sans o_text-xs o_text-dark_light" style="font-family: Helvetica, Arial, sans-serif;margin-top: 0px;margin-bottom: 0px;font-size: 14px;line-height: 21px;max-width: 584px;color: #a0a3ab;">
                        <p class="o_mb" style="margin-top: 0px;margin-bottom: 16px;">
                            <a class="o_text-dark_light" href="https://sellhub.io" style="text-decoration: none;outline: none;color: #a0a3ab;">
                                <img src="{{ asset('email/images/favico.png') }}" width="30" height="30" alt="Sellhub" style="max-width: 36px;-ms-interpolation-mode: bicubic;vertical-align: middle;border: 0;line-height: 100%;height: auto;outline: none;text-decoration: none;">
                            </a>
                        </p>
                        <p class="o_mb" style="margin-top: 0px;margin-bottom: 16px;">{{ date('Y') }} Sellhub Portal</p>

                        <p style="margin-top: 0px;margin-bottom: 0px;">
                            <a class="o_text-dark_light o_underline" href="https://sellhub.io/contact" style="text-decoration: underline;outline: none;color: #a0a3ab;">Contact Center</a>
                            <span class="o_hide-xs">&nbsp; • &nbsp;</span><br class="o_hide-lg" style="display: none;font-size: 0;max-height: 0;width: 0;line-height: 0;overflow: hidden;mso-hide: all;visibility: hidden;">
                            <a class="o_text-dark_light o_underline" href="https://sellhub.io/tos" style="text-decoration: underline;outline: none;color: #a0a3ab;">TOS</a>
                        </p>
                    </div>
                    <div class="o_hide-xs" style="font-size: 64px; line-height: 64px; height: 64px;">&nbsp;</div>
                </td>
            </tr>
        </tbody>
    </table>
</body>

</html>
