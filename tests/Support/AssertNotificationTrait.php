<?php

namespace Tests\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Assert as PHPUnit;

trait AssertNotificationTrait
{
    protected function assertNotificationEquals($user, $notifiableClass, $data)
    {
        Notification::assertSentTo(
            $user,
            $notifiableClass,
            function ($notification, $channels) use ($user, $data, $notifiableClass) {
                if (Arr::has($data, 'mail')) {
                    $this->assertNotificationToMail($notification, $channels, $user, $data, $notifiableClass);
                }

                if (Arr::has($data, 'broadcast')) {
                    $this->assertNotificationToBroadcast($notification, $channels, $user, $data, $notifiableClass);
                }

                if (Arr::has($data, 'database')) {
                    $this->assertNotificationToDatabase($notification, $channels, $user, $data, $notifiableClass);
                }

                return true;
            }
        );
    }

    private function assertNotificationToMail($notification, $channels, $user, $data, $notifiableClass)
    {
        PHPUnit::assertTrue(
            in_array('mail', $channels),
            "The expected [{$notifiableClass}] notification to mail was not sent."
        );

        $mailData = $notification->toMail($user);
        $subject = Arr::get($data['mail'], 'subject');

        if (!empty($subject)) {
            $this->assertEquals($subject, $mailData->subject);
        }

        $this->assertEquals(
            $this->getFixture($data['mail']['fixture']),
            view($mailData->view, $mailData->getData())->render(),
            "Fixture {$data['mail']['fixture']} does not equals rendered mail."
        );
    }

    private function assertNotificationToBroadcast($notification, $channels, $user, $data, $notifiableClass)
    {
        PHPUnit::assertTrue(
            in_array('broadcast', $channels),
            "The expected [{$notifiableClass}] notification to broadcast was not sent."
        );

        $broadcastData = $notification->toBroadcast($user);

        $this->assertEquals(
            $data['broadcast'],
            $broadcastData->data,
            "Filed asserting broadcast message in [{$notifiableClass}]"
        );
    }

    private function assertNotificationToDatabase($notification, $channels, $user, $data, $notifiableClass)
    {
        PHPUnit::assertTrue(
            in_array('database', $channels),
            "The expected [{$notifiableClass}] notification to database was not sent."
        );

        $databaseData = $notification->toDatabase($user);

        $this->assertEquals(
            $data['database'],
            $databaseData,
            "Filed asserting database data in {$notifiableClass}"
        );
    }
}
