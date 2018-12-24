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

use Baidu\Iov\DmKit\Policy\PolicyTrigger;

class SessionObject
{
    private $state;
    private $slots;
    private $context;

    public function __construct()
    {
        $this->slots = [];
        $this->context = [];
        $this->state = PolicyTrigger::INIT_STATE;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     * @return SessionObject
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param mixed $context
     * @return SessionObject
     */
    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSlots()
    {
        return $this->slots;
    }

    /**
     * @param mixed $slots
     * @return SessionObject
     */
    public function setSlots($slots)
    {
        $this->slots = $slots;
        return $this;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getSlot($key)
    {
        return $this->slots[$key][0];
    }
}
