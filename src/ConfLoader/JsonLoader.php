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

namespace Baidu\Iov\DmKit\ConfLoader;

class JsonLoader implements LoaderInterface
{
    private $path;
    private $cache;

    /**
     * JsonLoader constructor.
     * @param $path
     */
    function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @return array
     */
    public function load()
    {
        if($this->cache) {
            $strConf = $this->cache->get($this->getKey());
            if(empty($strConf)) {
                $strConf = file_get_contents($this->path);
                $this->cache->set($this->getKey(), $strConf);
            }
            return json_decode($strConf, true);
        }
        return json_decode(file_get_contents($this->path), true);
    }

    /**
     * @param CacheInterface $cache
     * @return void
     */
    public function setCache(CacheInterface $cache = null)
    {
        $this->cache = $cache;
    }

    /**
     * @return string
     */
    private function getKey()
    {
        return 'dm_conf_' . md5($this->path);
    }
}
