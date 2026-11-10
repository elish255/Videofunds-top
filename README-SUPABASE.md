# VideoFunds — Supabase setup

This project is configured for a **new, empty Supabase project**. It does not import Firebase data and does not require Firebase or Redis.

## 1. Create the database
1. Open the Supabase project SQL Editor.
2. Run `supabase-schema.sql` once.
3. In Authentication → Providers → Email, keep Email/Password enabled and turn OFF **Confirm email**. Users will still register and log in with email + password, but they will not need to verify their email. The registration handler also attempts an immediate password login if signup does not return a session.
4. Create a normal user account through the site, then run this SQL using the exact admin email:

   ```sql
   update public.profiles set is_admin=true where email=lower('YOUR_ADMIN_EMAIL');
   ```

## 2. Vercel environment variables
Set these for Production, Preview and Development as needed:

- `SUPABASE_URL` — Project URL (`https://<project-ref>.supabase.co`)
- `SUPABASE_ANON_KEY` — publishable/anon key
- `SUPABASE_SERVICE_ROLE_KEY` — secret service-role key (server-side only; never put it in browser JavaScript or commit it to Git)

Do not add Firebase or Redis variables. Rotate any database password or service-role key that has been exposed in chat, screenshots, logs, or source control.

## 3. Deploy
Use the repository's existing Vercel Docker configuration (`Dockerfile.vercel`). The PHP runtime is FrankenPHP and the app is served from `public/`.

## Current migration scope
- Supabase Auth is used for email/password signup, login, refresh and logout.
- Profile, wallet, video-watch, transaction, withdrawal, notification and referral tables are defined in PostgreSQL.
- Video reward RPC enforces a fixed `$3.40` reward, 70 seconds minimum as reported by the client, a 50-per-day cap and a unique `(user_id, video_id)` record.
- Auth tokens are held in secure, HttpOnly cookies; PHP filesystem sessions and Redis are not used for customer login.

**Important:** The video duration check currently relies on the browser-reported 70 seconds. It is not production-grade anti-fraud yet: a server-issued watch-session token and server-side elapsed-time validation are still required before enabling real-money withdrawals. Verify signup, email confirmation settings, profile creation, reward claims, and withdrawals against the live Supabase project before opening the site to users.
