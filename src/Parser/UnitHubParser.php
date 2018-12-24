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

namespace Baidu\Iov\DmKit\Parser;

use Baidu\Iov\DmKit\Dialog\QuResult;
use Baidu\Iov\DmKit\Dialog\Slot;
use Baidu\Iov\DmKit\Policy\PolicyTrigger;
use Monolog\Logger;

/**
 * for unit hub api
 *
 * Class UnitHubParser
 * @package Baidu\Iov\DmKit\Parser
 */
class UnitHubParser implements ParserInterface
{

    /**
     * @var $logger Logger
     */
    private $logger;
    /**
     * @var
     */
    private $quResultMap;

    /**
     * UnitHubParser constructor.
     * @param Logger $logger
     */
    function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param $response
     * @return array
     */
    public function parse($response)
    {
        $this->logger->debug('Parse unit hub response: '. json_encode($response));
        if ($response['errno'] != 0) {
            $this->logger->warning('Parse unit response failed. Unit respond: ' . json_encode($response));
        }

        $quResultMap = [];
        if(!$response['unit_response']) {
            return $quResultMap;
        }
        foreach ($response['unit_response'] as $unitResponse) {
            if (!$unitResponse['bot_id']) {
                continue;
            }

            $quResult = new QuResult();
            $quResult->setBotId($unitResponse['bot_id']);
            $intent = empty($unitResponse['response']['schema']['intent']) ? PolicyTrigger::NON_INTENT : $unitResponse['response']['schema']['intent'];
            $quResult->setIntent($intent);
            $slots = $unitResponse['response']['schema']['slots'];
            $slotsMap = [];
            foreach ($slots as $unitSlot) {
                $slot = new Slot();
                $slot->setKey($unitSlot['name'])
                    ->setValue($unitSlot['original_word'])
                    ->setNormalizedValue($unitSlot['normalized_word'])
                    ->setBegin($unitSlot['begin']);
                $slotsMap[] = $slot;
            }
            usort($slotsMap, function($a, $b) {
                return $a->getBegin() < $b->getBegin() ? -1 : 1;
            });
            foreach ($slotsMap as $item) {
                $quResult->addSlot($item);
            }
            $quResultMap[$unitResponse['bot_id']] = $quResult;
        }

        foreach ($response['bot_session_list'] as $session) {
            $quResult = $quResultMap[$session['bot_id']];
            if(!$quResult) {
                $this->logger->warning('Load session failed for bot '. $session['bot_id']);
                continue;
            }
            $quResult->setSessionId($session['bot_session_id']);
        }

        $this->quResultMap = $quResultMap;
        return $quResultMap;
    }

}
