-- Admin-managed YouTube library. Server-side PHP uses the service role; clients cannot access this table directly.
create table if not exists public.vf_video_library (
 youtube_id text primary key check (youtube_id ~ '^[A-Za-z0-9_-]{11}$'),
 title text not null check (char_length(btrim(title)) between 1 and 150),
 is_active boolean not null default true,
 created_at timestamptz not null default now()
);
alter table public.vf_video_library enable row level security;
revoke all on table public.vf_video_library from public, anon, authenticated;
grant select, insert, update, delete on table public.vf_video_library to service_role;

create or replace function public.vf_claim_video_reward(p_video_id text,p_video_title text default '',p_watch_seconds integer default 0)
returns jsonb language plpgsql security definer set search_path=public as $$
declare v_uid uuid:=auth.uid(); v_balance numeric(14,2); v_count integer; v_today date:=current_date; v_reward constant numeric(10,2):=3.40; v_catalog_title text;
begin
 if v_uid is null then raise exception 'Not authenticated'; end if;
 if coalesce(trim(p_video_id),'')='' then raise exception 'Video ID is required'; end if;
 if coalesce(p_watch_seconds,0)<70 then raise exception 'Watch the video for at least 70 seconds'; end if;
 select title into v_catalog_title from public.vf_video_library where youtube_id=p_video_id and is_active=true;
 if found then p_video_title:=v_catalog_title;
 elsif exists(select 1 from public.vf_video_library where youtube_id=p_video_id and is_active=false) then raise exception 'Video is no longer available';
 end if;
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
revoke all on function public.vf_claim_video_reward(text,text,integer) from public;
grant execute on function public.vf_claim_video_reward(text,text,integer) to authenticated;
notify pgrst, 'reload schema';
