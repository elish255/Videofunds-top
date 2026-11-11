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
