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

use PHPUnit\Framework\TestCase;
use Baidu\Iov\DmKit\Logger\LoggerFactory;
use Baidu\Iov\DmKit\Parser\ParserFactory;
use Baidu\Iov\DmKit\Parser\UnitBotParser;
use Baidu\Iov\DmKit\Dialog\QuResult;
use Baidu\Iov\DmKit\Dialog\Slot;

class UnitBotParserTest extends TestCase
{
    /**
     * @return \Baidu\Iov\DmKit\Parser\ParserInterface
     */
    public function testParserFactory()
    {
        $logger = LoggerFactory::getInstance([
            'handler' => 'stream',
            'args' => [
                'php://stderr',
                'critical'
            ]
        ]);
        $parser = ParserFactory::getInstance([
            'type' => 'unit_bot',
        ], $logger);

        $this->assertInstanceOf(UnitBotParser::class, $parser);
        return $parser;
    }

    /**
     * @depends testParserFactory
     * @param UnitBotParser $parser
     * @return mixed
     */
    public function testUnitBotParser(UnitBotParser $parser)
    {
        $response = json_decode(file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'response/response1.json'), true);
        $quResultMap = $parser->parse($response);
        $this->assertArrayHasKey('11505', $quResultMap);
        $quResult = $quResultMap['11505'];
        $this->assertInstanceOf(QuResult::class, $quResult);
        return $quResult;
    }

    /**
     * @depends testUnitBotParser
     * @param QuResult $quResult
     */
    public function testQuResult(QuResult $quResult)
    {
        $this->assertEquals('INTENT_ADJUST_QUOTA', $quResult->getIntent());
        $slots = $quResult->getSlots();
        foreach ($slots as $slot) {
            $slot = current($slot);
            $this->assertInstanceOf(Slot::class, $slot);
            $this->assertEquals('user_method', $slot->getKey());
        }
    }

}