-- VideoFunds membership stages: Activation, Exchange and Tax.
-- Run once in Supabase SQL Editor. Existing users are mapped using their actual payment records.
alter table public.vf_profiles
  add column if not exists membership text not null default 'ACTIVATION';

alter table public.vf_profiles
  drop constraint if exists vf_profiles_membership_check;

alter table public.vf_profiles alter column membership set default 'ACTIVATION';

update public.vf_profiles
set membership = case
  when tax_payment_status = 'paid' then 'TAX'
  when exchange_payment_status = 'paid' then 'TAX'
  when activation_payment_status = 'paid' or activated = true then 'EXCHANGE'
  else 'ACTIVATION'
end;

alter table public.vf_profiles
  add constraint vf_profiles_membership_check
  check (membership in ('ACTIVATION','EXCHANGE','TAX'));

create index if not exists vf_profiles_membership_idx
  on public.vf_profiles(membership);

notify pgrst, 'reload schema';
