<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\SystemAnnouncementNotification;
use Illuminate\Console\Command;

/**
 * Broadcast a plain ZREMS test email to users.
 *
 * SAFETY DEFAULTS:
 *  - --dry-run is implicit unless --send is given.
 *  - Prompts for confirmation before dispatching.
 *  - Skips users without a valid email.
 *  - Throttles: 1 second between sends by default (adjust with --sleep-ms).
 *  - Optional --limit and --only-domain filters.
 *
 * Usage examples:
 *   php artisan lams:broadcast-test                          # dry run, prints list
 *   php artisan lams:broadcast-test --limit=5                # dry run, first 5
 *   php artisan lams:broadcast-test --send --limit=5         # actually send 5
 *   php artisan lams:broadcast-test --send --only-domain=zesco.co.zm
 *   php artisan lams:broadcast-test --send --to=15,42        # only these user ids
 */
class BroadcastTestEmailCommand extends Command
{
    protected $signature = 'lams:broadcast-test
                            {--send : Actually send. Without this flag the command is a dry run.}
                            {--limit= : Max number of users to include.}
                            {--only-domain= : Restrict to users whose email ends with @<domain>.}
                            {--to= : Comma-separated user IDs (overrides other filters).}
                            {--subject=ZREMS Notification System - Test Message : Email subject.}
                            {--sleep-ms=1000 : Milliseconds to wait between sends (throttle SMTP).}';

    protected $description = 'Send a plain test email to ZREMS users (dry-run by default).';

    public function handle(): int
    {
        $send        = (bool) $this->option('send');
        $limit       = $this->option('limit') ? (int) $this->option('limit') : null;
        $onlyDomain  = trim((string) $this->option('only-domain'));
        $toIds       = trim((string) $this->option('to'));
        $subject     = (string) $this->option('subject');
        $sleepMs     = max(0, (int) $this->option('sleep-ms'));

        // Build recipient set
        $query = User::query()->whereNotNull('email')->where('email', '!=', '');

        if ($toIds !== '') {
            $ids = collect(explode(',', $toIds))->map(fn ($v) => (int) trim($v))->filter()->all();
            $query->whereIn('id', $ids);
        } elseif ($onlyDomain !== '') {
            $query->where('email', 'like', '%@' . ltrim($onlyDomain, '@'));
        }

        if ($limit) $query->limit($limit);

        $users = $query->get(['id', 'name', 'email']);

        if ($users->isEmpty()) {
            $this->warn('No users matched the filters.');
            return self::SUCCESS;
        }

        // Filter out anything that isn't a real email
        $users = $users->filter(fn ($u) => filter_var($u->email, FILTER_VALIDATE_EMAIL))->values();

        $this->info("Recipients matched: {$users->count()}");
        $this->table(['ID', 'Name', 'Email'], $users->take(20)->map(fn ($u) => [$u->id, $u->name, $u->email])->all());
        if ($users->count() > 20) {
            $this->line('... and ' . ($users->count() - 20) . ' more');
        }

        if (!$send) {
            $this->comment('DRY RUN — no emails sent. Add --send to actually dispatch.');
            return self::SUCCESS;
        }

        if (!$this->confirm("Send test email to {$users->count()} recipient(s) via " . config('mail.default') . '?', false)) {
            $this->warn('Aborted.');
            return self::SUCCESS;
        }

        $body = $this->buildBody();
        $sent = 0; $failed = 0;

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $u) {
            try {
                $u->notify(new SystemAnnouncementNotification(
                    title: $subject,
                    bodyMarkdown: $body,
                    ctaLabel: 'Open ZREMS',
                    ctaUrl: url('/'),
                    category: 'scoping',
                ));
                $sent++;
            } catch (\Throwable $e) {
                $failed++;
                $this->newLine();
                $this->error("FAIL {$u->email}: " . $e->getMessage());
            }

            $bar->advance();
            if ($sleepMs > 0) usleep($sleepMs * 1000);
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Sent: {$sent} | Failed: {$failed}");

        return self::SUCCESS;
    }

    protected function buildBody(): string
    {
        return
            "Hello,\n\n" .
            "This is a test message from ZREMS (ZESCO Real Estate Management System).\n\n" .
            "If you received this email, ZREMS can reach your inbox successfully. No action is required.\n\n" .
            "You will start receiving ZREMS notifications for events relevant to you\n" .
            "(scoping tasks, approvals, sign-offs, digests, etc.).\n\n" .
            "You can control your notification preferences at any time by logging into ZREMS\n" .
            "and visiting: Settings → Notification Preferences.\n\n" .
            "Timestamp: " . now()->toDateTimeString() . "\n\n" .
            "— ZREMS System";
    }
}
