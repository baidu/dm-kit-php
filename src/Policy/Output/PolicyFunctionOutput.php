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
use Baidu\Iov\DmKit\Policy\Policy;
use Baidu\Iov\DmKit\Session\AbstractSession;
use Monolog\Logger;

class PolicyFunctionOutput implements PolicyOutputInterface
{
    /**
     * @var $policy Policy
     */
    public $policy;
    private $function;

    /**
     * @var $logger Logger
     */
    private $logger;

    /**
     * PolicyFunctionOutput constructor.
     * @param $function
     */
    public function __construct($function)
    {
        $this->function = $function;
    }

    /**
     * @param $logger
     * @return $this
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @param AbstractSession $session
     * @return mixed
     * @throws DmException
     */
    public function output(AbstractSession $session)
    {
        $function = $this->function;
        $this->logger->debug('Output function: '. $function);
        if(method_exists($this->policy->policyManager->getBot(), $function)) {
            $output = $this->policy->policyManager->getBot()->$function();
            return $output;
        }else{
            throw new DmException("Method '$function' doesn't exist.");
        }
    }

    /**
     * @param Policy $policy
     */
    public function setPolicy(Policy $policy)
    {
        $this->policy = $policy;
    }
}