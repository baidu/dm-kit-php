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
use Baidu\Iov\DmKit\ConfLoader\LoaderFactory;
use Baidu\Iov\DmKit\ConfLoader\JsonLoader;
use Baidu\Iov\DmKit\ConfLoader\YamlLoader;

class ConfLoaderTest extends TestCase
{
    public function testJsonLoader()
    {
        $loaderConf = [
            'type' => 'json',
        ];

        $loader = LoaderFactory::getInstance($loaderConf, $this->getConfigPath() . DIRECTORY_SEPARATOR . 'quota_adjust.json');
        $this->assertInstanceOf(JsonLoader::class, $loader);
        $conf = $loader->load();
        $this->assertArrayHasKey('policies', $conf);
    }

    public function testYamlLoader()
    {
        $loaderConf = [
            'type' => 'yaml',
        ];

        $loader = LoaderFactory::getInstance($loaderConf, $this->getConfigPath() . DIRECTORY_SEPARATOR . 'quota_adjust.yml');
        $this->assertInstanceOf(YamlLoader::class, $loader);
        $conf = $loader->load();
        $this->assertArrayHasKey('policies', $conf);
    }

    /**
     * @return string
     */
    private function getConfigPath()
    {
        return dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'app/config';
    }
}