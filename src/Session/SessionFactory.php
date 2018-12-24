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

namespace Baidu\Iov\DmKit\Session;

use Baidu\Iov\DmKit\Exception\DmException;
use Monolog\Logger;

class SessionFactory
{
    /**
     * @param $conf
     * @param Logger $logger
     * @param $botId
     * @return AbstractSession
     * @throws DmException
     */
    public static function getInstance($conf, $logger, $botId)
    {
        switch ($conf['type']) {
            case 'file':
                $session = new FileSession($logger, $conf);
                break;
            case 'custom':
                $class= $conf['class'];
                if(!class_exists($class)) {
                    throw new DmException("Session class '$class' doesn't exist.");
                }
                $session = new $class($logger, $conf);
                if(!$session instanceof AbstractSession) {
                    throw new DmException('Session class should extend Baidu\Iov\DmKit\Session\AbstractSession');
                }
                break;
            default:
                $session = new FileSession($logger, $conf);
        }
        $session->setBotId($botId);
        return $session;
    }
}