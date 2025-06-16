<?php
echo "Running cron.php...\n";

require_once __DIR__ . '/functions.php';

sendXKCDUpdatesToSubscribers();

echo "cron.php finished.\n";
