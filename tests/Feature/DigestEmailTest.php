<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

class DigestEmailTest extends TestCase
{
public function test_is_digest_email_command_will_execute_at_10_am()
    {
        $schedule = new \Illuminate\Console\Scheduling\Schedule;
        $schedule->command('daily:digest')->dailyAt('10:00');

        $events = collect($schedule->events());

        $digest_events = $events->filter(function ($event) {
            return stripos($event->command, 'daily:digest');
        });

        $digest_events->each(function (Event $event) {
            // 10 = 10AM
            $this->assertEquals($event->expression, '0 10 * * *');
        });
    }
}
