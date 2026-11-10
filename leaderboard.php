<?php
require_once __DIR__.'/supabase.php';

$leaderboard = [];
try {
    // Use each user's saved local currency and USD exchange rate.
    $r = sb_request(
        'GET',
        '/rest/v1/vf_profiles?select=full_name,balance,currency_code,exchange_rate&order=balance.desc&limit=10'
    );

    foreach (($r['data'] ?? []) as $u) {
        $balance = (float)($u['balance'] ?? 0);
        $rate = (float)($u['exchange_rate'] ?? 1);
        if ($rate <= 0) $rate = 1;

        $currency = strtoupper((string)($u['currency_code'] ?? 'USD'));
        if ($currency === '') $currency = 'USD';

        $symbol = ($currency === 'TZS') ? 'TSh' : $currency;

        $leaderboard[] = [
            'name' => !empty($u['full_name']) ? $u['full_name'] : 'Anonymous',
            'balance' => $balance,
            'local_balance' => $balance * $rate,
            'currency' => $currency,
            'symbol' => $symbol
        ];
    }
} catch (Throwable $e) {
    error_log('Leaderboard: '.$e->getMessage());
}
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
        .local-balance { display:block; color:#18610A; font-weight:600; margin-top:3px; }
        .usd-balance { font-weight:600; }
    </style>
</head>
<body>
<div class="container">
    <h1 class="text-center mb-4">🏆 Top Earners Leaderboard</h1>
    <table class="table table-bordered table-striped">
        <thead>
            <tr><th>Rank</th><th>Full Name</th><th>Balance (USD / Local)</th></tr>
        </thead>
        <tbody>
            <?php if (empty($leaderboard)): ?>
                <tr><td colspan="3" class="text-center">No users found</td></tr>
            <?php else: ?>
                <?php $rank = 1; foreach ($leaderboard as $user): ?>
                <tr>
                    <td><?php echo $rank++; ?></td>
                    <td><?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <span class="usd-balance">$<?php echo number_format($user['balance'], 2); ?></span>
                        <span class="local-balance">
                            <?php echo htmlspecialchars($user['symbol'], ENT_QUOTES, 'UTF-8'); ?>
                            <?php echo number_format($user['local_balance'], 2); ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <a href="dashboard.php" class="btn btn-secondary">⬅ Back to Dashboard</a>
</div>
<?php require __DIR__ . '/includes/site-footer.php'; ?>
</body>
</html>
