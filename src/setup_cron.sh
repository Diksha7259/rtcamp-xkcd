#!/bin/bash

CRON_CMD="php $(pwd)/cron.php"
CRON_JOB="0 9 * * * $CRON_CMD"

(crontab -l 2>/dev/null; echo "$CRON_JOB") | sort -u | crontab -
echo "✅ CRON job added to send XKCD daily at 9:00 AM"
