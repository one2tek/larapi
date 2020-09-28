<?php

namespace one2tek\larapi\Formatters;

use Monolog\Handler\SlackWebhookHandler;

class SlackFormatter
{
    /**
     * Customize the given logger instance.
     *
     * @param  \Illuminate\Log\Logger  $logger
     *
     * @return void
     */
    public function __invoke($logger)
    {
        $extraData = [];
        
        if (class_exists(config("larapi-components.slack_formatter"))) {
            $extraData = config("larapi-components.slack_formatter")::data();
        }

        foreach ($logger->getHandlers() as $handler) {
            if ($handler instanceof SlackWebhookHandler) {
                $handler->pushProcessor(function ($record) use ($extraData) {
                    foreach ($extraData as $key => $extraRecord) {
                        $record['extra'][$key] = $extraRecord;
                    }

                    return $record;
                });
            }
        }
    }
}
