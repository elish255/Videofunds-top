# Withdrawal flow (Activation -> Exchange -> Tax -> Final)

**Run `public/supabase-withdrawal-flow-migration.sql` once in Supabase SQL Editor before deploying.**
It adds the withdrawal draft columns on `vf_profiles` and two atomic functions
(`vf_submit_withdrawal`, `vf_admin_set_withdrawal`).

User flow
1. Dashboard > Withdraw money (`choosepayment.php`): methods + **Withdrawal History** (all the user's withdrawals).
2. Method page (`enterphonenumber.php` etc.) saves a *draft* (`process_withdrawal.php`) -> `endpay.php` (Activation fee).
3. Activation verified -> `index-exchange.php` (amount + phone, balance shown above green text) -> `exchange.php` (Confirmation, Exchange fee, "Make Payment").
4. `paymentmethod.php?stage=exchange` -> verified -> `newindex.php` (Tax fee notice) -> `paymentmethod.php?stage=tax`.
5. Tax verified -> `confirmation1.php`: submits the withdrawal once (balance deducted, status **Pending**), shows final message, user returns to dashboard and the cycle restarts.
6. Admin panel > Withdrawals: Pending -> Approve -> Mark as Paid (or Reject + refund). The user gets a notification each time.

"Dear <username>" uses the account full name (e-mail prefix as fallback).
Root and `public/` copies are kept in sync.
