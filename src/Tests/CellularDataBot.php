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

namespace Baidu\Iov\DmKit\Tests;

use Baidu\Iov\DmKit\Bot\Bot;

/**
 * This class is for cellular data bot test
 * Functions are for demo purpose which returns mock data.
 * In real application, these might need to invoke a remote service call.
 *
 * Class CellularDataBot
 * @package Baidu\Iov\DmKit\Tests
 */
class CellularDataBot extends Bot
{

    /**
     * @param $time
     * @return int
     */
    public function demo_get_cellular_data_left($time)
    {
        $elements = explode('-', $time);
        if (count($elements) > 1 && $elements[1] === "01") {
            echo "left 0 \n";
            return 0;
        } else {
            echo "left 2 \n";
            return 2;
        }
    }

    /**
     * @param $time
     * @return int
     */
    public function demo_get_cellular_data_usage($time)
    {
        $elements = explode('-', $time);
        if (count($elements) > 1 && $elements[1] === "01") {
            return 0;
        } else {
            return 2;
        }
    }

    /**
     * @param $type
     * @return string
     */
    public function demo_get_package_options($type)
    {
        if ($type === "省内流量包") {
            return "10元100M，20元300M";
        } else if ($type === "全国流量包") {
            return "10元100M，50元1G";
        } else {
            return "20元300M，50元1G";
        }
    }

}