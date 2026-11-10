-- VideoFunds fresh Supabase database. Run in Supabase SQL Editor.
create extension if not exists pgcrypto;
create table if not exists public.vf_profiles (
 id uuid primary key references auth.users(id) on delete cascade,
 full_name text not null default '', email text not null default '',
 balance numeric(14,2) not null default 5.00 check(balance>=0),
 referral_code text unique not null, referred_by text,
 country_code text not null default 'TZ', currency_code text not null default 'USD',
 exchange_rate numeric(14,4) not null default 1,
 daily_watch_count integer not null default 0 check(daily_watch_count>=0),
 last_watch_date date not null default current_date,
 activated boolean not null default false, banned boolean not null default false,
 is_admin boolean not null default false, created_at timestamptz not null default now(), updated_at timestamptz not null default now()
);
create table if not exists public.vf_watched_videos (
 id bigint generated always as identity primary key, user_id uuid not null references public.vf_profiles(id) on delete cascade,
 video_id text not null, video_title text not null default '', reward numeric(10,2) not null default 3.40 check(reward=3.40),
 watched_at timestamptz not null default now(), unique(user_id,video_id)
);
create index if not exists vf_watched_videos_user_date_idx on public.vf_watched_videos(user_id,watched_at desc);
create table if not exists public.vf_transactions (
 id bigint generated always as identity primary key, user_id uuid not null references public.vf_profiles(id) on delete cascade,
 type text not null check(type in ('video_reward','referral_bonus','withdrawal','admin_credit','admin_debit','deposit')),
 amount numeric(14,2) not null, description text not null default '', reference text unique, created_at timestamptz not null default now()
);
create index if not exists transactions_user_date_idx on public.vf_transactions(user_id,created_at desc);
create table if not exists public.vf_withdrawals (
 id bigint generated always as identity primary key, user_id uuid not null references public.vf_profiles(id) on delete cascade,
 amount numeric(14,2) not null check(amount>0), method text not null default '', account_number text not null default '',
 account_name text not null default '', status text not null default 'pending' check(status in ('pending','approved','paid','rejected')),
 admin_note text not null default '', created_at timestamptz not null default now(), updated_at timestamptz not null default now()
);
create table if not exists public.vf_notifications (
 id bigint generated always as identity primary key, user_id uuid references public.vf_profiles(id) on delete cascade,
 title text not null, body text not null, is_read boolean not null default false, created_at timestamptz not null default now()
);
create table if not exists public.vf_referral_bonuses (
 id bigint generated always as identity primary key, referrer_id uuid not null references public.vf_profiles(id) on delete cascade,
 referred_user_id uuid not null unique references public.vf_profiles(id) on delete cascade,
 amount numeric(10,2) not null default 2.00, claimed boolean not null default false, created_at timestamptz not null default now()
);
-- Atomic reward claim: fixed $3.40, at least 70 seconds, max 50/day, unique user/video.
create or replace function public.vf_claim_video_reward(p_video_id text,p_video_title text default '',p_watch_seconds integer default 0)
returns jsonb language plpgsql security definer set search_path=public as $$
declare v_uid uuid:=auth.uid(); v_balance numeric(14,2); v_count integer; v_today date:=current_date; v_reward constant numeric(10,2):=3.40;
begin
 if v_uid is null then raise exception 'Not authenticated'; end if;
 if coalesce(trim(p_video_id),'')='' then raise exception 'Video ID is required'; end if;
 if coalesce(p_watch_seconds,0)<70 then raise exception 'Watch the video for at least 70 seconds'; end if;
 perform 1 from public.vf_profiles where id=v_uid and banned=false for update;
 if not found then raise exception 'Account unavailable'; end if;
 if exists(select 1 from public.vf_watched_videos where user_id=v_uid and video_id=p_video_id) then raise exception 'You have already watched this video'; end if;
 update public.vf_profiles set daily_watch_count=case when last_watch_date=v_today then daily_watch_count+1 else 1 end,last_watch_date=v_today,balance=balance+v_reward,updated_at=now()
 where id=v_uid and (case when last_watch_date=v_today then daily_watch_count else 0 end)<50 returning balance,daily_watch_count into v_balance,v_count;
 if not found then raise exception 'Daily limit reached (50 videos)'; end if;
 insert into public.vf_watched_videos(user_id,video_id,video_title,reward) values(v_uid,p_video_id,left(coalesce(p_video_title,''),250),v_reward);
 insert into public.vf_transactions(user_id,type,amount,description,reference) values(v_uid,'video_reward',v_reward,'Video reward: '||left(coalesce(p_video_title,p_video_id),200),'VID-'||gen_random_uuid()::text);
 return jsonb_build_object('success',true,'new_balance',v_balance,'daily_watch_count',v_count,'reward',v_reward);
end; $$;
alter table public.vf_profiles enable row level security;
alter table public.vf_watched_videos enable row level security;
alter table public.vf_transactions enable row level security;
alter table public.vf_withdrawals enable row level security;
alter table public.vf_notifications enable row level security;
alter table public.vf_referral_bonuses enable row level security;
create policy "profiles own read" on public.vf_profiles for select to authenticated using(auth.uid()=id);
create policy "watched own read" on public.vf_watched_videos for select to authenticated using(auth.uid()=user_id);
create policy "transactions own read" on public.vf_transactions for select to authenticated using(auth.uid()=user_id);
create policy "withdrawals own read" on public.vf_withdrawals for select to authenticated using(auth.uid()=user_id);
create policy "notifications own read" on public.vf_notifications for select to authenticated using(user_id is null or auth.uid()=user_id);
create policy "referral own read" on public.vf_referral_bonuses for select to authenticated using(auth.uid()=referrer_id);
revoke all on function public.vf_claim_video_reward(text,text,integer) from public;
grant execute on function public.vf_claim_video_reward(text,text,integer) to authenticated;
-- Promote an administrator manually after signup:
-- update public.vf_profiles set is_admin=true where email='YOUR_ADMIN_EMAIL';
