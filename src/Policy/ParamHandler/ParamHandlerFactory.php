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

namespace Baidu\Iov\DmKit\Policy\ParamHandler;


use Baidu\Iov\DmKit\Exception\DmException;
use Baidu\Iov\DmKit\Policy\Policy;

class ParamHandlerFactory
{
    /**
     * @param $type
     * @param Policy $policy
     * @param $value
     * @param $options
     * @return ParamHandlerInterface
     * @throws DmException
     */
    public static function getInstance($type, Policy $policy, $value, $options)
    {
        switch ($type) {
            case 'slot_val':
                return new SlotValHandler($policy, $value);
            case 'ori_slot_val':
                return new OriSlotValHandler($policy, $value);
            case 'session_state':
                return new SessionStateHandler($policy, $value);
            case 'qu_intent':
                return new QuIntentHandler($policy, $value);
            case 'func_val':
                return new FuncValHandler($policy, $value);
            case 'request_param':
                return new RequestParamHandler($policy, $value);
            case 'session_context':
                return new SessionContextHandler($policy, $value);
            case 'json_extractor':
                return new JsonExtractorHandler($policy, $value);
            case 'http_request':
                return new HttpRequestHandler($policy, $value, $options);
            default:
                throw new DmException("Param type $type is not supported.");
        }
    }
}