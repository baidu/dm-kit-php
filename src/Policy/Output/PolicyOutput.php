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

namespace Baidu\Iov\DmKit\Policy\Output;

use Baidu\Iov\DmKit\Exception\DmException;
use Baidu\Iov\DmKit\Policy\Output\Assertion\AssertionFactory;
use Baidu\Iov\DmKit\Policy\Policy;
use Baidu\Iov\DmKit\Session\AbstractSession;
use Monolog\Logger;

class PolicyOutput implements PolicyOutputInterface
{
    /**
     * @var $policy Policy
     */
    public $policy;

    private $assertion;
    private $session;
    private $result;

    /**
     * @var $logger Logger
     */
    private $logger;

    /**
     * PolicyOutput constructor.
     * @param $assertion
     * @param $session
     * @param $result
     */
    public function __construct($assertion, $session, $result)
    {
        $this->assertion = $assertion;
        $this->session = $session;
        $this->result = $result;
    }

    /**
     * @param Logger $logger
     * @return PolicyOutput
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @param AbstractSession $session
     * @return bool|mixed
     */
    public function output(AbstractSession $session)
    {
        if ($this->assert()) {
            if(empty($this->session['state'])) {
                $session->clean();
            }
            $session->getSessionObject()->setState($this->session['state']);
            $context = $session->getSessionObject()->getContext();

            $newContext = $this->session['context'];
            array_walk_recursive($newContext, function(&$item) {
                $item = $this->policy->replaceParams($item);
            });
            $session->getSessionObject()->setContext(array_merge($context, $newContext));

            $results = [];
            $standardOutput = $this->policy->policyManager->getBot()->getStandardOutput();
            foreach ($this->result as $item) {
                $data = $item['value'];
                if ($item['type'] === 'json') {
                    $data = $this->replaceParams($data);
                    $standardOutput = $this->policy->policyManager->getBot()->getStandardOutput($data);
                    $results[] = ['type' => 'json', 'value' => $data];
                } else {
                    $data = $this->replaceParams($data);
                    $results[] = ['type' => $item['type'], 'value' => $data];
                }
            }
            return array_merge($standardOutput, ['results' => $results]);
        }

        return false;
    }

    /**
     * @return bool
     * @throws DmException
     */
    private function assert()
    {
        if(!$this->assertion) {
            return true;
        }
        foreach ($this->assertion as $assertion) {
            if (empty($assertion['type']) || empty($assertion['value'])) {
                return false;
            }

            $value = $this->policy->replaceParams($assertion['value']);
            $type = $assertion['type'];
            $assertion = AssertionFactory::getInstance($type);
            if(false === $assertion->assert($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Policy $policy
     * @return mixed|void
     */
    public function setPolicy(Policy $policy)
    {
        $this->policy = $policy;
    }

    /**
     * @param $data
     * @return array|mixed
     */
    private function replaceParams($data)
    {
        if(is_string($data)){
            $data = $this->policy->replaceParams($data);
        }elseif(is_array($data)) {
            array_walk_recursive($data, function(&$item) {
                $item = $this->policy->replaceParams($item);
            });
        }

        return $data;
    }
}