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

/**
 * Basic file session, only works on single server architecture
 *
 * Class FileSession
 * @package Baidu\Iov\DmKit\Session
 */
class FileSession extends AbstractSession
{
    /**
     * @return SessionObject
     */
    public function read()
    {
        $filename = $this->getFilename();
        if (!is_file($filename)) {
            $this->sessionObject = new SessionObject();
        } elseif (time() - filemtime($filename) > $this->expire) {
            unlink($filename);
            $this->sessionObject = new SessionObject();
        } else {
            $this->sessionObject = unserialize(file_get_contents($filename));
        }
        $this->logger->debug('Read Session: ' . serialize($this->sessionObject));
        return $this->sessionObject;
    }

    /**
     * write file session
     */
    public function write()
    {
        $filename = $this->getFilename();
        if ($this->shouldDelete && file_exists($filename)) {
            $this->logger->debug('Delete Session: ' . $this->getKey());
            unlink($filename);
        } else {
            $this->logger->debug('Write Session: ' . serialize($this->sessionObject));
            file_put_contents($filename, serialize($this->sessionObject));
        }
    }

    /**
     * @return string
     */
    private function getFilename()
    {
        $path = isset($this->conf['path']) ? $this->conf['path'] : '/tmp/dm_session/';
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $filename = $path . 'session_' . $this->getKey();
        return $filename;
    }
}
