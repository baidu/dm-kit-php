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

class PolicyTriggerScore
{
    private $intentScore = 0;
    private $stateScore = 0;
    private $slotsScore = 0;
    private $changedSlotsScore = 0;

    /**
     * @param PolicyTriggerScore $policyTriggerScore
     * @return bool
     */
    public function isGreaterThan(PolicyTriggerScore $policyTriggerScore)
    {
        if ($this->intentScore > $policyTriggerScore->getIntentScore()) {
            return true;
        }
        if ($this->stateScore > $policyTriggerScore->getStateScore()) {
            return true;
        }
        if ($this->slotsScore > $policyTriggerScore->getSlotsScore()) {
            return true;
        }
        if ($this->changedSlotsScore > $policyTriggerScore->getChangedSlotsScore()) {
            return true;
        }
        return false;
    }

    /**
     * @param mixed $intentScore
     * @return PolicyTriggerScore
     */
    public function setIntentScore($intentScore)
    {
        $this->intentScore = $intentScore;
        return $this;
    }

    /**
     * @param mixed $stateScore
     * @return PolicyTriggerScore
     */
    public function setStateScore($stateScore)
    {
        $this->stateScore = $stateScore;
        return $this;
    }

    /**
     * @param mixed $slotsScore
     * @return PolicyTriggerScore
     */
    public function setSlotsScore($slotsScore)
    {
        $this->slotsScore = $slotsScore;
        return $this;
    }

    /**
     * @param mixed $changedSlotsScore
     * @return PolicyTriggerScore
     */
    public function setChangedSlotsScore($changedSlotsScore)
    {
        $this->changedSlotsScore = $changedSlotsScore;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIntentScore()
    {
        return $this->intentScore;
    }

    /**
     * @return mixed
     */
    public function getStateScore()
    {
        return $this->stateScore;
    }

    /**
     * @return mixed
     */
    public function getSlotsScore()
    {
        return $this->slotsScore;
    }

    /**
     * @return mixed
     */
    public function getChangedSlotsScore()
    {
        return $this->changedSlotsScore;
    }


}