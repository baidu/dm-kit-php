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

namespace Baidu\Iov\DmKit;

use Baidu\Iov\DmKit\Bot\Bot;
use Baidu\Iov\DmKit\ConfLoader\LoaderFactory;
use Baidu\Iov\DmKit\Exception\DmException;
use Baidu\Iov\DmKit\Logger\LoggerFactory;
use Baidu\Iov\DmKit\Parser\ParserFactory;
use Baidu\Iov\DmKit\Policy\PolicyManager;
use Baidu\Iov\DmKit\Session\SessionFactory;

class PolicyManagerFactory
{

    static private $defaultConfs = ['loader' => ['type' => 'json']];

    /**
     * entry of the dmkit, build PolicyManager
     *
     * @param $botId
     * @param $confPath
     * @param array $confs
     * @return PolicyManager
     * @throws DmException
     */
    static public function getInstance($botId, $confPath, $confs = [])
    {
        $confs = array_merge(self::$defaultConfs, $confs);
        $loader = LoaderFactory::getInstance($confs['loader'], $confPath);
        $conf = $loader->load();
        $logger = LoggerFactory::getInstance($conf['logger']);
        $parser = ParserFactory::getInstance($conf['parser'], $logger);
        $session = SessionFactory::getInstance($conf['session'], $logger, $botId);

        $policies = $conf['policies'];
        $policyManager = new PolicyManager($session, $parser, $logger);
        $retryLimit = isset($conf['retry_limit']) ? $conf['retry_limit'] : 0;
        if (isset($conf['bot'])) {
            $botClass = $conf['bot'];
            if (!class_exists($botClass)) {
                throw new DmException("Bot class '$botClass' doesn't exist.");
            }

            $bot = new $botClass($session, $retryLimit);
            if (!$bot instanceof Bot) {
                throw new DmException('Bot class should extend Baidu\Iov\DmKit\Bot\Bot');
            }
        } else {
            $bot = new Bot($session, $retryLimit);
        }

        $policyManager->setBotId($botId)->setBot($bot)->load($policies);

        return $policyManager;
    }

}