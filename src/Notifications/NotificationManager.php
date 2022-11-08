<?php

namespace Dovu\GuardianPhpSdk\Notifications;

class NotificationManager
{
    public function __construct(private $settings)
    {
        
    }

    public function register(\Exception $error)
    {
        foreach($this->settings->notifications as $notification){
            $notifier = AbstractNotifier::getNotifier($notification);
            $notifier->send($error);
        }
    }
}
