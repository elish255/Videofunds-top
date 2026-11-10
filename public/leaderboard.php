<?php
require_once __DIR__.'/supabase.php';
$leaderboard=[];
try{$r=sb_request('GET','/rest/v1/profiles?select=full_name,balance&order=balance.desc&limit=10');foreach($r['data'] as $u)$leaderboard[]=['name'=>$u['full_name']?:'Anonymous','balance'=>(float)$u['balance']];}catch(Throwable $e){error_log('Leaderboard: '.$e->getMessage());}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - Top Earners</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <style>
        body { background: #f8f9fa; font-family: 'Inter', sans-serif; }
        .container { max-width: 800px; margin-top: 50px; }
        .table th { background: #68162e; color: white; }
        h1 { color: #18610A; }
    </style>
</head>
<body>
<div class="container">
    <h1 class="text-center mb-4">🏆 Top Earners Leaderboard</h1>
    <table class="table table-bordered table-striped">
        <thead>
            <tr><th>Rank</th><th>Full Name</th><th>Balance (USD)</th></tr>
        </thead>
        <tbody>
            <?php if (empty($leaderboard)): ?>
                <tr><td colspan="3" class="text-center">No users found</td></tr>
            <?php else: ?>
                <?php $rank = 1; foreach ($leaderboard as $user): ?>
                <tr>
                    <td><?php echo $rank++; ?></td>
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                    <td>$<?php echo number_format($user['balance'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <a href="dashboard.php" class="btn btn-secondary">⬅ Back to Dashboard</a>
</div>
</body>
</html>