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

namespace Baidu\Iov\DmKit\Bot;

use Baidu\Iov\DmKit\Dialog\QuResult;
use Baidu\Iov\DmKit\Session\AbstractSession;

class Bot
{
    private $retryLimit;

    /**
     * @var $session AbstractSession
     */
    protected $session;

    /**
     * @var $quResult QuResult
     */
    protected $quResult;

    protected $requestParams;

    /**
     * Bot constructor.
     * @param AbstractSession $session
     * @param $retryLimit
     */
    public function __construct(AbstractSession $session, $retryLimit)
    {
        $this->session = $session;
        $this->retryLimit = $retryLimit;
    }

    /**
     * @param QuResult $quResult
     * @return Bot
     */
    public function setQuResult($quResult)
    {
        $this->quResult = $quResult;
        return $this;
    }

    /**
     * @param mixed $requestParams
     * @return Bot
     */
    public function setRequestParams($requestParams)
    {
        $this->requestParams = $requestParams;
        return $this;
    }

    /**
     * @param $key
     * @param $value
     */
    protected function setSessionContext($key, $value)
    {
        $context = $this->session->getSessionObject()->getContext();
        $context[$key] = $value;
        $this->session->getSessionObject()->setContext($context);
    }

    /**
     * @param $key
     * @return mixed
     */
    protected function getSessionContext($key)
    {
        $context = $this->session->getSessionObject()->getContext();
        return $context[$key];
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getSlot($key)
    {
        return $this->session->getSessionObject()->getSlot($key);
    }

    /**
     * @param $cardType
     * @param $intent
     * @param $tts
     * @param array $data
     * @return array
     */
    protected function result($cardType, $intent, $tts = '', $data = array())
    {
        $retryTime = $this->getSessionContext('retry_time');
        if (!$retryTime) {
            $this->setSessionContext('last_card_type', $cardType);
            $this->setSessionContext('last_intent', $intent);
            $this->setSessionContext('last_tts', $tts);
            $this->setSessionContext('last_data', $data);
            $this->setSessionContext('retry_tts', null);
        }

        return array_merge($this->getStandardOutput($data), ['results' =>
            [['type' => 'json', 'value' => array_merge([
                'card_type' => $cardType,
                'intent' => $intent,
                'tts' => $tts,
            ], $data)]]
        ]);
    }

    /**
     * set the next state, reset the retry time
     *
     * @param $state
     */
    protected function setState($state)
    {
        $this->setSessionContext('retry_time', 0);
        $this->session->getSessionObject()->setState($state);
    }

    /**
     * @param $tts
     * @return $this
     */
    protected function setRetryTts($tts)
    {
        $this->setSessionContext('retry_tts', $tts);
        return $this;
    }

    /**
     * @return array|bool
     */
    public function retry()
    {
        $cardType = $this->getSessionContext('last_card_type');
        $intent = $this->getSessionContext('last_intent');
        $tts = $this->getSessionContext('retry_tts') ? $this->getSessionContext('retry_tts') : $this->getSessionContext('last_tts');
        $data = $this->getSessionContext('last_data');
        $retryTime = $this->getSessionContext('retry_time');
        $this->setSessionContext('retry_time', $retryTime + 1);
        if ($retryTime >= $this->retryLimit) {
            //when exceeding the limit of retry, clean the session and exit
            $this->session->clean();
            return false;
        } else {
            return $this->result($cardType, $intent, $tts, $data);
        }
    }

    /**
     * @param array $data
     * @return array
     */
    public function getStandardOutput(&$data = [])
    {
        if(key_exists('bot_session_id', $data)) {
            $sessionId = $data['bot_session_id'];
            unset($data['bot_session_id']);
        }else{
            $sessionId = $this->quResult->getSessionId();
        }

        // you can set a confidence score for the result
        // default value is 100
        if(key_exists('score', $data)) {
            $score = intval($data['score']);
            unset($data['score']);
        }else{
            $score = 100;
        }

        if($this->session->getShouldDelete()){
            $sessionId = '';
        }

        return [
            'raw_query' => $this->requestParams['word'] ?? '',
            'bot_id' => $this->quResult->getBotId(),
            'bot_session_id' => $sessionId,
            'score' => $score,
        ];
    }
}