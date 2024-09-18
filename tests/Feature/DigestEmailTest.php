<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

class DigestEmailTest extends TestCase
{
    public function test_is_digest_email_command_scheduled_at_10_am()
    {
        // Ensure that console output is not mocked
        $this->withoutMockingConsoleOutput();

        // Manually call the Kernel to bootstrap schedule
        $kernel = $this->app->make(\Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();

        // Get the schedule
        $schedule = app(Schedule::class);

        // Filter for the 'daily:digest' command
        $events = collect($schedule->events())->filter(function (Event $event) {
            return str_contains($event->command, 'daily:digest');
        });

        // Check if any events were found
        if ($events->isEmpty()) {
            $this->fail('No events found for the daily:digest command.');
        }

        // Assert that the event is scheduled at 10 AM
        $events->each(function (Event $event) {
            $this->assertEquals('0 10 * * *', $event->expression, 'The daily:digest command is not scheduled at 10:00 AM.');
        });
    }
}
