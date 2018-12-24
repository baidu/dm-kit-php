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

namespace Baidu\Iov\DmKit\Request;

use GuzzleHttp\Client;

class RequestUnit
{
    const URL = 'https://aip.baidubce.com/rpc/2.0/unit/bot/chat';
    const LOG_ID_PREFIX = 'dmkit_';

    /**
     * @param $accessToken
     * @param array $payload
     * post parameters for unit, see https://ai.baidu.com/docs#/UNIT-v2-API/top
     * there is an example in src/Tests/speak.php
     *
     * @return mixed
     * @throws \Exception
     */
    public function requestUnit($accessToken, $payload)
    {
        if (!isset($payload['log_id'])) {
            throw new \Exception('log_id is required.');
        }

        $payload['log_id'] = self::LOG_ID_PREFIX . $payload['log_id'];
        $header = ['Content-Type: application/json'];
        $ret = $this->sendPost(self::URL . '?access_token=' . $accessToken, json_encode($payload), $header);
        $ret = json_decode($ret, true);
        if(!isset($ret['error_code']) || $ret['error_code'] != 0) {
            throw new \Exception('Unit request failed, error_code: '. $ret['error_code']. ', error_msg: '. $ret['error_msg']);
        }
        return $ret;
    }

    /**
     * @param $url
     * @param $data
     * @param $header
     * @return mixed
     */
    private function sendPost($url, $data, $header = [])
    {
        $client = new Client();
        $res = $client->request('post', $url, ['body' => $data, 'header' => $header]);
        return $res->getBody();
    }
}