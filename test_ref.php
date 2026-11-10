<?php
echo "<h2>Referral Test</h2>";
echo "URL: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "GET ref parameter: " . ($_GET['ref'] ?? 'NOT SET') . "<br>";
?>