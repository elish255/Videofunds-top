# SplashPay live payment setup

## Server environment variables
Set these as server-side environment variables in the hosting control panel. Do not put them in HTML, JavaScript, or a `VITE_` variable:

- `SUPABASE_URL`
- `SUPABASE_ANON_KEY`
- `SUPABASE_SERVICE_ROLE_KEY`
- `SPLASHPAY_API_KEY`
- `SPLASHPAY_API_SECRET`

## Supabase migration
Run the `vf_payments` table section at the bottom of `supabase-schema.sql` in Supabase SQL Editor. The application uses the existing `vf_profiles` fields for stage status and references.

## SplashPay API
The integration uses the endpoints and headers supplied for this project:

- `POST https://api.splashpay.co.tz/api/v1/payments/mobile-money`
- `POST https://api.splashpay.co.tz/api/v1/payments/check-status`
- Headers: `X-API-KEY`, `X-API-SECRET`

The server initiates a push payment and checks the provider status before changing the user's payment stage to paid. It never marks a payment successful from a browser redirect or user-submitted status.

## Important before going live
1. Confirm the exact SplashPay response fields and status vocabulary with your merchant dashboard/API documentation. The code follows the response structure in the integration sample you provided.
2. Confirm your merchant is enabled for TZS mobile-money collection and test using a low-value authorized transaction.
3. Ensure PHP cURL is enabled on the hosting account and the website is served over HTTPS.
4. The SplashPay secret and Supabase service-role key must exist only in server environment variables.
5. Configure hosting to block public access to `*.sql`, `*.md`, logs, and backup/configuration files.

The previous `payment-demo.php` simulation endpoint has been removed. No successful payment is assumed; SplashPay must report a matching amount, currency, and reference.


## Integrated VideoFunds payment page
The main payment page is `paymentmethod.php?stage=activation|exchange|tax`. It uses the VideoFunds site background and maroon theme, starts SplashPay mobile-money push requests through the authenticated `payment-gateway.php` server endpoint, and polls `payments/check-status` through the same endpoint every five seconds. The USSD instructions display the amount for the selected payment stage dynamically. Keep the `vf_payments` migration and all required server environment variables configured.
