<?php

namespace Gentritabazi01\LarapiComponents\Core;

use Monolog\Logger;
use Monolog\Handler\SlackWebhookHandler;
use Illuminate\Support\Facades\Auth;

class SlackLogger
{
    /**
     * Customize the given logger instance.
     *
     * @param  \Illuminate\Log\Logger  $logger
     * @return void
     */
    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            if ($handler instanceof SlackWebhookHandler) {
                $handler->pushProcessor(function ($record) {
                    $record['extra']['App Env'] = config('app.env');
                    $record['extra']['Date & Time'] = date('Y-m-d H:i:s');
                    if (Auth::check()) {
                        $record['extra']['User Email'] = Auth::user()->email;
                    }
                    if (request()->getPathInfo()) {
                        $record['extra']['Request URL'] = asset(request()->getPathInfo());
                    }
                    $record['extra']['Method'] = request()->getMethod();
                    if (strlen(json_encode(request()->all())) > 2) {
                        $record['extra']['Parameters'] = json_encode(request()->all());
                    }
                    if (isset($_SERVER['HTTP_ORIGIN'])) {
                        $record['extra']['Origin'] = $_SERVER['HTTP_ORIGIN'];
                    }
                    if (isset($_SERVER['HTTP_REFERER'])) {
                        $record['extra']['Referer'] = $_SERVER['HTTP_REFERER'];
                    }

                    return $record;
                });
            }
        }
    }
}
