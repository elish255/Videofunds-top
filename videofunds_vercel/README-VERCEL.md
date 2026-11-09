# VideoFunds — Vercel PHP Container deployment

This package keeps the existing PHP site and Firebase project (`videofunds-project`) and adds the Admin Panel, WhatsApp channel shortcut, user notifications, account ban/delete actions, and a more defensive referral-bonus claim flow.

## Admin

- Admin login: `/admin/login.php`
- Only `yohanaelisha164@gmail.com` is allow-listed.
- The account must also exist in Firebase Authentication and have a valid password.
- Admin can list/search users, view an individual account, ban/unban, delete the Firebase Auth account and Firestore user document, and send notifications to all users or one UID.
- Admin actions are written to the `admin_audit` Firestore collection.
- User notifications are stored in `notifications` and displayed on the user dashboard.
- The WhatsApp floating link is injected on public pages and opens `https://whatsapp.com/channel/0029VbE0Zw95a23zlb8rKk0R`.

## Required Vercel setup before deploying

1. Create/link a Vercel project and connect this folder/repository.
2. Add a Redis-compatible database from the Vercel Marketplace and set `REDIS_URL`. PHP's default file sessions are not durable/shared across Vercel container instances; Redis is required for reliable login sessions.
3. In Firebase Console, create a service account for the existing project `videofunds-project`. Give it the least required IAM roles: **Cloud Datastore User** for Firestore and **Firebase Authentication Admin** for user management. Generate a JSON key and paste the entire one-line JSON into `FIREBASE_SERVICE_ACCOUNT_JSON` in Vercel. Never commit the key file.
4. Add these Environment Variables in Vercel (Production, Preview, and Development as appropriate):
   - `FIREBASE_API_KEY` — Firebase web API key from Project settings.
   - `FIREBASE_PROJECT_ID` — `videofunds-project`.
   - `FIREBASE_SERVICE_ACCOUNT_JSON` — full service-account JSON as one-line JSON.
   - `REDIS_URL` — Redis connection URL from the Vercel Marketplace.
   - `PAYMENT_ACCESS_KEY` — newly rotated payment access key.
   - `PAYMENT_SECRET_KEY` — newly rotated payment secret key.
   - `PAYMENT_API_URL` — real payment initiation endpoint from your provider.
   - `PAYMENT_STATUS_URL` — real payment status endpoint from your provider.
   - `PAYMENT_MERCHANT_ID` — current merchant ID if still valid.
5. Redeploy after adding or changing environment variables.
6. Confirm `/` loads, then `/admin/login.php`, then test admin actions using a test account before production use.

## Important security and integration notes

- The payment access/secret keys that were hard-coded in the uploaded archive have been removed from the app code. Treat them as exposed: **revoke/rotate them at the payment provider before going live**. The Firebase web API key is a browser key, not an admin credential; restrict it in Google Cloud API-key settings to the Firebase APIs and your domains.
- The uploaded `payment-api-proxy.php` contained a placeholder host (`api.yourpaymentprovider.com`) and the older `payment-proxy.php` only simulated a successful payment by writing local JSON files. Vercel containers have ephemeral local storage, so that simulation has been disabled. Real payment processing cannot be completed until the provider's actual endpoint, request/response format, and webhook signature rules are supplied and configured. The app now fails closed instead of pretending a payment succeeded.
- Vercel containers are stateless. Runtime logs and JSON files in the old archive are not a persistent database. User data remains in Firestore; sessions use Redis. If you rely on transaction history from the old JSON files, migrate it to Firestore before launch.
- Review `firestore.rules.example` and deploy an appropriate Firestore security ruleset. The example is explicitly transitional because the existing application still performs some user balance/earning writes directly from PHP using user tokens. A full financial-grade hardening pass should move all balance/earning mutations to trusted server endpoints and then deny client-side balance writes.
- Deleting a user removes the Firebase Authentication account, root Firestore profile, and known `pending_bonuses` subcollection entries. Other untracked subcollections, if any, should be reviewed before enabling bulk deletion.
- Ban disables the Firebase Authentication account and sets `banned=true` on the Firestore profile. Protected PHP pages check that flag. Already-issued Firebase ID tokens may remain valid until expiry; the page guard also checks the profile flag on each protected page request.

## Local run

Use Docker with the same container image:

```bash
docker build -f Dockerfile.vercel -t videofunds .
docker run --rm -p 8080:80 --env-file .env videofunds
```

Open `http://localhost:8080`.
