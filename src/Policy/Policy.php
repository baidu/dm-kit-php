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

namespace Baidu\Iov\DmKit\Policy;

use Baidu\Iov\DmKit\Exception\DmException;
use Baidu\Iov\DmKit\Policy\Output\PolicyOutputInterface;

class Policy
{
    private $policyTrigger;
    private $policyParams;
    private $policyOutputs;

    /**
     * @var $policyManager PolicyManager
     */
    public $policyManager;

    /**
     * Policy constructor.
     * @param PolicyTrigger $policyTrigger
     * @param $policyParams
     * @param $policyOutputs
     * @param PolicyManager $policyManager
     */
    public function __construct(PolicyTrigger $policyTrigger, $policyParams, $policyOutputs, PolicyManager $policyManager)
    {
        $this->policyTrigger = $policyTrigger;
        $this->policyParams = $policyParams;
        $this->policyOutputs = $policyOutputs;
        $this->policyManager = $policyManager;

        $this->policyTrigger->policy = $this;
        foreach ($this->policyOutputs as $policyOutput) {
            /**
             * @var $policyOutput PolicyOutputInterface
             */
            $policyOutput->setPolicy($this);
        }
        foreach ($this->policyParams as $policyParam) {
            /**
             * @var $policyParam PolicyParam
             */
            $policyParam->policy = $this;
        }
    }

    /**
     * @return PolicyTrigger
     */
    public function getPolicyTrigger()
    {
        return $this->policyTrigger;
    }

    /**
     * @return mixed
     */
    public function getPolicyParams()
    {
        return $this->policyParams;
    }

    /**
     * @return mixed
     */
    public function getPolicyOutputs()
    {
        return $this->policyOutputs;
    }

    /**
     * @param $str
     * @return mixed
     * @throws DmException
     */
    public function replaceParams($str)
    {
        $params = $this->getPolicyParams();
        $matches = [];
        preg_match_all('/{%([a-zA-Z\-_]+)%}/', $str, $matches);
        foreach ($matches[1] as $key => $match) {
            /**
             * @var $param PolicyParam
             */
            $param = $params[$match];
            if (!$param) {
                throw new DmException('Param ' . $match . ' not exists.');
            }
            $value = $param->getParam();
            if (is_array($value)) {
                $value = json_encode($value);
                $str = str_replace($matches[0][$key], $value, $str);
                $str = json_decode($str, true);
            } else {
                $str = str_replace($matches[0][$key], $value, $str);
            }
        }

        return $str;
    }
}