-- Educational withdrawal-flow state. This migration stores progress so users resume where they stopped.
alter table public.vf_profiles add column if not exists activation_payment_status text not null default 'unpaid';
alter table public.vf_profiles add column if not exists activation_payment_reference text;
alter table public.vf_profiles add column if not exists activation_paid_at timestamptz;
alter table public.vf_profiles add column if not exists exchange_payment_status text not null default 'unpaid';
alter table public.vf_profiles add column if not exists exchange_payment_reference text;
alter table public.vf_profiles add column if not exists exchange_paid_at timestamptz;
alter table public.vf_profiles add column if not exists tax_payment_status text not null default 'unpaid';
alter table public.vf_profiles add column if not exists tax_payment_reference text;
alter table public.vf_profiles add column if not exists tax_paid_at timestamptz;
alter table public.vf_profiles add column if not exists withdrawal_flow_stage text not null default 'choose_withdrawal';

alter table public.vf_profiles drop constraint if exists vf_profiles_activation_payment_status_check;
alter table public.vf_profiles add constraint vf_profiles_activation_payment_status_check check (activation_payment_status in ('unpaid','pending','paid','cancelled'));
alter table public.vf_profiles drop constraint if exists vf_profiles_exchange_payment_status_check;
alter table public.vf_profiles add constraint vf_profiles_exchange_payment_status_check check (exchange_payment_status in ('unpaid','pending','paid','cancelled'));
alter table public.vf_profiles drop constraint if exists vf_profiles_tax_payment_status_check;
alter table public.vf_profiles add constraint vf_profiles_tax_payment_status_check check (tax_payment_status in ('unpaid','pending','paid','cancelled'));
alter table public.vf_profiles drop constraint if exists vf_profiles_withdrawal_flow_stage_check;
alter table public.vf_profiles add constraint vf_profiles_withdrawal_flow_stage_check check (withdrawal_flow_stage in ('choose_withdrawal','activation','exchange','tax','withdrawal_processing','completed'));

notify pgrst, 'reload schema';


-- Manual LIPA NAMBA payment verification queue for admins.
create table if not exists public.vf_manual_payment_requests (
 id bigint generated always as identity primary key,
 user_id uuid not null references public.vf_profiles(id) on delete cascade,
 stage text not null check(stage in ('activation','exchange','tax')),
 phone text not null,
 amount numeric(14,2) not null check(amount>0),
 status text not null default 'pending' check(status in ('pending','approved','rejected')),
 created_at timestamptz not null default now(),
 reviewed_at timestamptz
);
create index if not exists vf_manual_payment_requests_status_created_idx on public.vf_manual_payment_requests(status,created_at desc);
create index if not exists vf_manual_payment_requests_user_idx on public.vf_manual_payment_requests(user_id,created_at desc);
alter table public.vf_manual_payment_requests enable row level security;
revoke all on public.vf_manual_payment_requests from anon,authenticated;
grant select,insert,update,delete on public.vf_manual_payment_requests to service_role;
grant usage,select on sequence public.vf_manual_payment_requests_id_seq to service_role;
notify pgrst, 'reload schema';
