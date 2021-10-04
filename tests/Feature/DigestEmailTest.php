<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

class DigestEmailTest extends TestCase
{
    public function test_is_digest_email_command_will_execute_at_10_am()
    {
        $schedule = app()->make(Schedule::class);

        $events = collect($schedule->events())->filter(function (Event $event) {
            return stripos($event->command, 'daily:digest');
        });

        if ($events->count() == 0) {
            $this->fail('No events found');
        }

        $events->each(function (Event $event) {
            // 10 = 10AM
            $this->assertEquals('0 10 * * *', $event->expression);
        });
    }
}
