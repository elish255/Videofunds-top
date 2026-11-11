-- VideoFunds: withdrawal request draft, atomic submit (balance deduction) and admin status changes.
-- Run this entire script ONCE in Supabase Dashboard > SQL Editor.

-- 1) Draft of the withdrawal the user is currently going through (Activation -> Exchange -> Tax).
alter table public.vf_profiles add column if not exists wd_amount numeric(14,2);
alter table public.vf_profiles add column if not exists wd_method text;
alter table public.vf_profiles add column if not exists wd_account text;

-- 2) Final step of the flow: deduct balance, create the Pending withdrawal, reset the cycle. Atomic + idempotent.
create or replace function public.vf_submit_withdrawal(p_user uuid)
returns jsonb language plpgsql security definer set search_path = public as $$
declare
  p public.vf_profiles%rowtype;
  v_id bigint;
  v_new_balance numeric(14,2);
begin
  select * into p from public.vf_profiles where id = p_user for update;
  if not found then raise exception 'Account not found'; end if;
  if p.banned then raise exception 'Account unavailable'; end if;
  if coalesce(p.wd_amount, 0) <= 0 then raise exception 'No pending withdrawal request'; end if;
  if not ((p.activation_payment_status = 'paid' or p.activated) and p.exchange_payment_status = 'paid' and p.tax_payment_status = 'paid') then
    raise exception 'Payments not complete';
  end if;
  if p.balance < p.wd_amount then raise exception 'Insufficient balance'; end if;

  insert into public.vf_withdrawals(user_id, amount, method, account_number, account_name, status)
  values (p_user, p.wd_amount, coalesce(p.wd_method, 'phone'), coalesce(p.wd_account, ''), '', 'pending')
  returning id into v_id;

  insert into public.vf_transactions(user_id, type, amount, description, reference)
  values (p_user, 'withdrawal', -p.wd_amount, 'Withdrawal request #' || v_id || ' (pending)', 'WD-' || v_id);

  update public.vf_profiles set
    balance = balance - p.wd_amount,
    wd_amount = null, wd_method = null, wd_account = null,
    activated = false,
    activation_payment_status = 'unpaid', activation_paid_at = null, activation_payment_reference = null,
    exchange_payment_status = 'unpaid', exchange_paid_at = null, exchange_payment_reference = null,
    tax_payment_status = 'unpaid', tax_paid_at = null, tax_payment_reference = null,
    membership = 'ACTIVATION', withdrawal_flow_stage = 'choose_withdrawal', updated_at = now()
  where id = p_user
  returning balance into v_new_balance;

  return jsonb_build_object('id', v_id, 'amount', p.wd_amount, 'new_balance', v_new_balance);
end; $$;

-- 3) Admin: Pending -> Approved -> Paid, or Rejected (refunds the amount to the user's balance).
create or replace function public.vf_admin_set_withdrawal(p_id bigint, p_status text, p_note text default '')
returns jsonb language plpgsql security definer set search_path = public as $$
declare
  w public.vf_withdrawals%rowtype;
begin
  if p_status not in ('approved', 'paid', 'rejected') then raise exception 'Invalid status'; end if;
  select * into w from public.vf_withdrawals where id = p_id for update;
  if not found then raise exception 'Withdrawal not found'; end if;
  if w.status in ('paid', 'rejected') then raise exception 'Withdrawal already %', w.status; end if;
  if w.status = 'approved' and p_status = 'approved' then raise exception 'Withdrawal already approved'; end if;

  if p_status = 'rejected' then
    update public.vf_profiles set balance = balance + w.amount, updated_at = now() where id = w.user_id;
    insert into public.vf_transactions(user_id, type, amount, description, reference)
    values (w.user_id, 'admin_credit', w.amount, 'Withdrawal #' || w.id || ' rejected - amount refunded', 'WDR-' || w.id);
  end if;

  update public.vf_withdrawals
     set status = p_status, admin_note = coalesce(p_note, ''), updated_at = now()
   where id = p_id;

  return jsonb_build_object('id', w.id, 'user_id', w.user_id, 'amount', w.amount, 'status', p_status);
end; $$;

revoke all on function public.vf_submit_withdrawal(uuid) from public, anon, authenticated;
revoke all on function public.vf_admin_set_withdrawal(bigint, text, text) from public, anon, authenticated;
grant execute on function public.vf_submit_withdrawal(uuid) to service_role;
grant execute on function public.vf_admin_set_withdrawal(bigint, text, text) to service_role;

create index if not exists vf_withdrawals_user_created_idx on public.vf_withdrawals(user_id, created_at desc);
create index if not exists vf_withdrawals_status_created_idx on public.vf_withdrawals(status, created_at desc);

notify pgrst, 'reload schema';
