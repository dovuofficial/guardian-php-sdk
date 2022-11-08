<?php

namespace Dovu\GuardianPhpSdk\Notifications;

use Exception;

abstract class AbstractNotifier
{
    public static function getNotifier($notification): AbstractNotifier
    {
        $notifier = array_keys($notification)[0];

        return match ($notifier) {
            'slack' => new SlackNotifier($notification[$notifier]),
        };
    }

    abstract public function send(Exception $error): void;
}
