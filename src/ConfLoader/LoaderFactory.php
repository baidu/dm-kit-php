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

use Baidu\Iov\DmKit\Exception\DmException;

class LoaderFactory
{
    /**
     * @param $conf
     * @param $path
     * @return LoaderInterface
     * @throws DmException
     */
    public static function getInstance($conf, $path)
    {
        switch ($conf['type']) {
            case 'json':
                $loader = new JsonLoader($path);
                break;
            case 'yaml':
                $loader = new YamlLoader($path);
                break;
            case 'custom':
                $class = $conf['class'];
                if(!class_exists($class)) {
                    throw new DmException("Loader class '$class' doesn't exist.");
                }
                $loader = new $class($path);
                if(!$loader instanceof LoaderInterface) {
                    throw new DmException('Loader class should implement Baidu\Iov\DmKit\ConfLoader\LoaderInterface');
                }
                break;
            default:
                throw new DmException('Conf loader is not set.');
        }

        if(!empty($conf['cache_class'])) {
            $cacheClass = $conf['cache_class'];
            if(!class_exists($cacheClass)) {
                throw new DmException("Cache class '$cacheClass' doesn't exist.");
            }
            $cache = new $cacheClass();
            if(!$cache instanceof CacheInterface) {
                throw new DmException('Cache class should implement Baidu\Iov\DmKit\ConfLoader\CacheInterface');
            }
            $loader->setCache($cache);
        }

        return $loader;
    }
}