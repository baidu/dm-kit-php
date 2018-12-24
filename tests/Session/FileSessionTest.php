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
use Baidu\Iov\DmKit\Session\SessionFactory;
use Baidu\Iov\DmKit\Logger\LoggerFactory;
use Baidu\Iov\DmKit\Session\FileSession;
use Baidu\Iov\DmKit\Session\SessionObject;

class FileSessionTest extends TestCase
{

    /**
     * @return \Baidu\Iov\DmKit\Session\AbstractSession
     */
    public function testFileSession()
    {
        $logger = LoggerFactory::getInstance([
            'handler' => 'stream',
            'args' => [
                'php://stderr',
                'critical'
            ]
        ]);
        $session = SessionFactory::getInstance([
            'type' => 'file',
            'expire' => 300
        ], $logger, 100);
        $this->assertInstanceOf(FileSession::class, $session);
        return $session;
    }

    /**
     * @depends testFileSession
     * @param FileSession $session
     */
    public function testInitRead(FileSession $session)
    {
        $sessionObject = $session->read();
        $this->assertInstanceOf(SessionObject::class, $sessionObject);
    }

    /**
     * @depends testFileSession
     * @param FileSession $session
     */
    public function testWriteSession(FileSession $session)
    {
        $sessionObject = $session->read();
        $sessionObject->setState('test_state');
        $sessionObject->setContext(['test_key' => 'test_value']);
        $session->write();

        $newSessionObject = $session->read();
        $this->assertEquals('test_state', $newSessionObject->getState());
        $this->assertEquals(['test_key' => 'test_value'], $newSessionObject->getContext());
    }

}