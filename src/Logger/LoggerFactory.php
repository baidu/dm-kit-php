<?php
// Copyright (c) 2018 Baidu, Inc. All Rights Reserved.
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//     http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.

namespace Baidu\Iov\DmKit\Logger;

use Baidu\Iov\DmKit\Exception\DmException;
use Monolog\Logger;
use Monolog\Handler\HandlerInterface;

class LoggerFactory
{
    /**
     * @param $conf
     * @return Logger
     * @throws DmException
     */
    public static function getInstance($conf)
    {
        $logger = new Logger('default');

        if(is_subclass_of($conf['handler'], HandlerInterface::class)){
            $handlerClass = $conf['handler'];
        }else{
            $handlerClass = self::getHandlerClassByType($conf['handler']);
        }

        if(!is_array($conf['args'])) {
            throw new DmException('Arguments for logger should be an array');
        }

        $handler = new $handlerClass(...$conf['args']);
        $logger->pushHandler($handler);

        return $logger;
    }

    /**
     * @param $handlerType
     * @return mixed
     */
    private static function getHandlerClassByType($handlerType)
    {
        $typeToClassMapping = array(
            'stream' => 'Monolog\Handler\StreamHandler',
            'group' => 'Monolog\Handler\GroupHandler',
            'buffer' => 'Monolog\Handler\BufferHandler',
            'deduplication' => 'Monolog\Handler\DeduplicationHandler',
            'rotating_file' => 'Monolog\Handler\RotatingFileHandler',
            'syslog' => 'Monolog\Handler\SyslogHandler',
            'syslogudp' => 'Monolog\Handler\SyslogUdpHandler',
            'null' => 'Monolog\Handler\NullHandler',
            'test' => 'Monolog\Handler\TestHandler',
            'gelf' => 'Monolog\Handler\GelfHandler',
            'rollbar' => 'Monolog\Handler\RollbarHandler',
            'flowdock' => 'Monolog\Handler\FlowdockHandler',
            'browser_console' => 'Monolog\Handler\BrowserConsoleHandler',
            'native_mailer' => 'Monolog\Handler\NativeMailerHandler',
            'socket' => 'Monolog\Handler\SocketHandler',
            'pushover' => 'Monolog\Handler\PushoverHandler',
            'raven' => 'Monolog\Handler\RavenHandler',
            'newrelic' => 'Monolog\Handler\NewRelicHandler',
            'hipchat' => 'Monolog\Handler\HipChatHandler',
            'slack' => 'Monolog\Handler\SlackHandler',
            'slackwebhook' => 'Monolog\Handler\SlackWebhookHandler',
            'slackbot' => 'Monolog\Handler\SlackbotHandler',
            'cube' => 'Monolog\Handler\CubeHandler',
            'amqp' => 'Monolog\Handler\AmqpHandler',
            'error_log' => 'Monolog\Handler\ErrorLogHandler',
            'loggly' => 'Monolog\Handler\LogglyHandler',
            'logentries' => 'Monolog\Handler\LogEntriesHandler',
            'whatfailuregroup' => 'Monolog\Handler\WhatFailureGroupHandler',
            'fingers_crossed' => 'Monolog\Handler\FingersCrossedHandler',
            'filter' => 'Monolog\Handler\FilterHandler',
            'mongo' => 'Monolog\Handler\MongoDBHandler',
            'elasticsearch' => 'Monolog\Handler\ElasticSearchHandler',
        );
        if (!isset($typeToClassMapping[$handlerType])) {
            throw new \InvalidArgumentException(sprintf('There is no handler class defined for handler "%s".', $handlerType));
        }
        return $typeToClassMapping[$handlerType];
    }
}