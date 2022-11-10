<?php

namespace Dovu\GuardianPhpSdk\Notifications;

abstract class AbstractNotifier
{
    public static function getNotifier(array $notification): AbstractNotifier
    {
        if (empty($notification)) {
            throw new \Exception("Notification array cannot be empty", 422);
        }

        $notifier = array_keys($notification)[0];

        return match ($notifier) {
            'slack' => new SlackNotifier($notification[$notifier]),
        };
    }

    abstract public function send(\Exception $error): void;
}
