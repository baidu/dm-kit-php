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


$rootPath = realpath(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR;
require $rootPath . 'vendor/autoload.php';

$bots = json_decode(file_get_contents($rootPath . 'app/config/bots.json'), true);
$stdin = fopen('php://stdin', 'r');
echo "Which bot would you like to test? \n";
echo "1.cellular data bot \n";
echo "2.quota adjust bot \n";
$choose = trim(fgets($stdin));
if ($choose == 1) {
    $policyManager = Baidu\Iov\DmKit\PolicyManagerFactory::getInstance($bots[0]['bot_id'], $rootPath . $bots[0]['conf_path']);
} elseif ($choose == 2) {
    $policyManager = Baidu\Iov\DmKit\PolicyManagerFactory::getInstance($bots[1]['bot_id'], $rootPath . $bots[1]['conf_path']);
} else {
    echo "You should choose 1 or 2. \n";
    exit;
}
echo "Entered bot, you can say something to test. \n";

$botSession = '';
while ($word = trim(fgets($stdin))) {
    try {
        $requestUnit = new \Baidu\Iov\DmKit\Request\RequestUnit();
        $payload = [
            'version' => '2.0',
            'bot_id' => $bots[$choose - 1]['bot_id'],
            'log_id' => rand(100000,999999),
            'request' => [
                'user_id' => 'test_user',
                'query' => $word,
                'query_info' => [
                    'type' => 'TEXT',
                    'source' => 'KEYBOARD',
                ],
                'bernard_level' => 0,
                'client_session' => '{"client_results":"", "candidate_options":[]}'
            ],
            'bot_session' => $botSession,
        ];
        //unit response
        $ret = $requestUnit->requestUnit($bots[$choose - 1]['access_token'], $payload);
        if(false === $ret) {
            echo "Request unit failed!";
            exit(-1);
        }
        //dm-kit output
        $output = $policyManager->setRequestParams(['cuid' => 'test_user'])->setQuResults($ret)->output();
        $botSession = $ret['result']['bot_session'];
        echo json_encode($output['results'], JSON_UNESCAPED_UNICODE) . "\n";
    } catch (\Exception $e) {
        echo $e->getMessage() . "\n";
        exit(-1);
    }
}