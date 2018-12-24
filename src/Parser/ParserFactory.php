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

use Baidu\Iov\DmKit\Exception\DmException;
use Monolog\Logger;

class ParserFactory
{
    /**
     * @param $conf
     * @param Logger $logger
     * @return ParserInterface
     * @throws DmException
     */
    public static function getInstance($conf, $logger)
    {
        $className = null;
        switch ($conf['type']) {
            case 'unit_hub':
                $className = UnitHubParser::class;
                break;
            case 'unit_bot':
                $className = UnitBotParser::class;
                break;
            case 'custom':
                if (empty($conf['class'])) {
                    throw new DmException('Parameter class must be defined when use custom parser.');
                }
                $className = $conf['class'];
                if(!class_exists($className)) {
                    throw new DmException("Parser class '$className' doesn't exist.");
                }
                break;
            default:
                throw new DmException('Parser is not set.');
        }

        $parser = new $className($logger);
        if(!$parser instanceof ParserInterface) {
            throw new DmException('Parser class should extend Baidu\Iov\DmKit\Parser\ParserInterface');
        }

        return $parser;
    }
}