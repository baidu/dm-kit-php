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

namespace Baidu\Iov\DmKit\Policy\Output\Assertion;

use Baidu\Iov\DmKit\Exception\DmException;

class AssertionFactory
{
    /**
     * @param $type
     * @return AssertionInterface
     * @throws DmException
     */
    public static function getInstance($type)
    {
        switch ($type) {
            case 'empty':
                return new EmptyAssertion();
            case 'not_empty':
                return new NotEmptyAssertion();
            case 'in':
                return new InAssertion();
            case 'not_in':
                return new NotInAssertion();
            case 'eq':
                return new EqAssertion();
            case 'gt':
                return new GtAssertion();
            case 'ge':
                return new GeAssertion();
            case 'not_eq':
                return new NotEqAssertion();
            default:
                if (is_subclass_of($type, AssertionInterface::class)) {
                    return new $type();
                } else {
                    throw new DmException("Assertion type $type is not supported.");
                }
        }
    }
}