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

namespace Baidu\Iov\DmKit\Policy\ParamHandler;


use Baidu\Iov\DmKit\Exception\DmException;

class JsonExtractorHandler extends AbstractHandler
{
    /**
     * @return mixed
     * @throws DmException
     */
    public function handle()
    {
        $json = explode(',', $this->value);
        $jsonObj = $this->policy->replaceParams($json[0]);
        var_dump($jsonObj);
        if(!is_array($jsonObj)) {
            throw new DmException("$json[0] is not a json object.");
        }
        $extractors = explode('.', $json[1]);
        foreach ($extractors as $extractor) {
            if (!isset($jsonObj[$extractor])) {
                return null;
            }
            $jsonObj = $jsonObj[$extractor];
        }
        return $jsonObj;
    }
}