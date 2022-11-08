<?php

namespace Dovu\GuardianPhpSdk\Notifications;

use Exception;

class SlackNotifier extends AbstractNotifier
{
    const SLACK_WEBHOOK = "https://hooks.slack.com/services/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";

    public function __construct(private $webhook)
    {
        
    }

    public function send(Exception $error): void
    {
        $message = json_decode($error->getMessage(), true);

        $c = curl_init($this->webhook);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($c, CURLOPT_POST, true);
        curl_setopt($c, CURLOPT_POSTFIELDS, $this->formatMessage($message));
        curl_exec($c);
        curl_close($c);
    }


    private function formatMessage($errorMessage): array
    {
        $code = $errorMessage['error']['statusCode'];
        $message = $errorMessage['error']['message'];
        $timestamp = $errorMessage['error']['timestamp'];
        $path = $errorMessage['error']['path'];

        return ['payload' => '{
            "text": "A Guardian request error has occurred:",
            "attachments": [{
            "color": "#f2c744",
            "blocks":
                [
                    {
                        "type": "section",
                                "text": 
                                    {
                                        "type": "mrkdwn",
                                         "text": "*Status Code:* '.$code.'\n*Message:* '.$message.'\n*Timestamp:* '.$timestamp.'\n*Path:* '.$path.'"
                                    },
                                
                    },
                    
                ]
            }]
        }'];
    }
}
