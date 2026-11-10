-- VideoFunds notification security migration
-- Run this in Supabase SQL Editor. It reuses the existing vf_notifications table.
alter table public.vf_notifications enable row level security;

drop policy if exists "notifications own read" on public.vf_notifications;
drop policy if exists "notifications own update" on public.vf_notifications;
drop policy if exists "notifications own delete" on public.vf_notifications;

create policy "notifications own read"
on public.vf_notifications
for select to authenticated
using (auth.uid() = user_id);

create policy "notifications own update"
on public.vf_notifications
for update to authenticated
using (auth.uid() = user_id)
with check (auth.uid() = user_id);

create policy "notifications own delete"
on public.vf_notifications
for delete to authenticated
using (auth.uid() = user_id);

grant select, update, delete on public.vf_notifications to authenticated;
grant select, insert, update, delete on public.vf_notifications to service_role;
notify pgrst, 'reload schema';
