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

namespace Baidu\Iov\DmKit\Dialog;

use Baidu\Iov\DmKit\Session\SessionObject;

/**
 * Class QuResult
 * @package Baidu\Iov\DmKit\Dialog
 */
class QuResult
{
    private $intent;
    private $slots;
    private $changedSlots;
    private $botId;
    private $sessionId;

    /**
     * QuResult constructor.
     */
    function __construct()
    {
        $this->slots = [];
        $this->changedSlots = [];
    }

    /**
     * @return mixed
     */
    public function getIntent()
    {
        return $this->intent;
    }

    /**
     * @param mixed $intent
     * @return QuResult
     */
    public function setIntent($intent)
    {
        $this->intent = $intent;
        return $this;
    }

    /**
     * @return Slot[][]
     */
    public function getSlots()
    {
        return $this->slots;
    }

    /**
     * @param Slot $slot
     * @return QuResult
     */
    public function addSlot(Slot $slot)
    {
        if (isset($this->slots[$slot->getKey()])) {
            $this->slots[$slot->getKey()][] = $slot;
        } else {
            $this->slots[$slot->getKey()] = [$slot];
        }

        return $this;
    }

    /**
     * @param $key
     * @return Slot
     */
    public function getSlot($key)
    {
        return $this->slots[$key][0];
    }

    /**
     * @return mixed
     */
    public function getBotId()
    {
        return $this->botId;
    }

    /**
     * @param mixed $botId
     * @return QuResult
     */
    public function setBotId($botId)
    {
        $this->botId = $botId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @param mixed $sessionId
     * @return QuResult
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
        return $this;
    }

    /**
     * @param SessionObject $sessionObject
     */
    public function buildChangedSlots(SessionObject $sessionObject)
    {
        $sessionSlotMap = $sessionObject->getSlots();
        foreach ($this->slots as $key => $slots) {
            $sessionSlots = $sessionSlotMap[$key] ?? [];
            if(count($slots) !== count($sessionSlots)) {
                $this->changedSlots[$key] = true;
                continue;
            }
            foreach ($slots as $i => $slot) {
                $sessionSlot = $sessionSlots[$i];
                if($slot->getValue() !== $sessionSlot->getValue()) {
                    $this->changedSlots[$key] = true;
                    break;
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getChangedSlots()
    {
        return $this->changedSlots;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $slots = [];
        foreach ($this->slots as $slotGroup) {
            foreach ($slotGroup as $slot) {
                $slots[] = $slot->getkey();
            }
        }
        return 'Intent: '. $this->intent. ', slots: '.json_encode($slots);
    }
}