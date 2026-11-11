<?php
// Compatibility bootstrap: auth is held in secure Supabase JWT cookies, not local/Redis sessions.
require_once __DIR__.'/supabase.php';
