# DM Kit 快速上手

## 简介

参考c++版本的[unit-dmkit](https://github.com/baidu/unit-dmkit)，完全兼容c++版本的policies配置文件，但是不能反向兼容。

在任务型对话系统（Task-Oriented Dialogue System）中，一般包括了以下几个模块：

* Automatic Speech Recognition（ASR），即语音识别模块，将音频转化为文本输入。
* Natural Language Understanding（NLU），即自然语言理解模块，通过分析文本输入，解析得到对话意图与槽位（Intent + Slots）。
* Dialog Manager（DM），即对话管理模块，根据NLU模块分析得到的意图+槽位值，结合当前对话状态，执行对应的动作并返回结果。其中执行的动作可能涉及到对内部或外部知识库的查询。
* Natural Language Generation（NLG），即自然语言生成。目前一般采用模板的形式。
* Text To Speech（TTS），即文字转语音模块，将对话系统的文本输出转化为音频。

DM Kit关注其中的对话管理模块（Dialog Manager），解决对话系统中状态管理、对话逻辑处理等问题。在实际应用中，单个垂类下对话逻辑一般都是根据NLU结果中意图与槽位值，结合当前对话状态，确定需要进行处理的子流程。子流程或者返回固定话术结果，或者根据NLU中槽位值与对话状态访问内部或外部知识库获取资源数据并生成话术结果返回，在返回结果的同时也对对话状态进行更新。我们将这部分对话处理逻辑进行抽象，提供一个通过配置快速构建对话流程，可复用的对话管理模块，即Reusable Dialog Manager。

## 如何使用

### 安装

使用composer安装
```
composer require baidu-chelianwang/dm-kit
```

### 使用

```
try{
    //botId为unit botid，confPath为配置文件地址，暂时只支持json格式
    $policyManager = PolicyManagerFactory::getInstance($botId, $confPath);
    $policyManager
        ->setRequestParams($arrInput) //设置客户端传入参数，必须有cuid字段，用来保存session
        ->setQuResults($quResults);   //设置nlu召回结果，对应的parser会做解析，支持unit直接返回结果
        
    //output即为召回结果，返回false则无召回
    $output = $policyManager->output();
}catch(DmException $e) {
    
} 
```

### 测试
1. 运行composer update，安装依赖库
2. 在百度unit平台上创建bot，具体参考[UNIT配置指南](docs/demo_skills.md)
3. 讲bot_id和access_token填入app/config/bots.json文件中，cellular_data为流量查询bot，quota_adjust为额度调整bot，access_token获取参考http://ai.baidu.com/docs#/Auth/top
4. 运行php src/Tests/speak.php，查看bot效果


## 详细配置说明

本节详细介绍实现垂类功能的配置语法。所有垂类的配置均位于模块源码app/conf目录下。

### 垂类注册

| 字段             |含义                         |
|-----------------|-----------------------------|
|bot            |bot类名，所有需要调用的函数都应该在这个类中          |
|retry_limit      |未召回时重试次数，不设置则不重试        |
|parser           |语义解析结果parser  	|
|+parser.type     |类型，可选unit，custom     |
|+parser.class    |若type为custom时必填，需实现ParserInterface |
|logger           | 使用monolog  |
|+logger.handler     |handler类型， |
|+logger.args    | handler构造函数中所需参数 |
|session          | 用于保存对话上下文信息                 |
|+session.type    |session类型，可选normal，custom |
|+session.class   |若type为custom时必填，需继承AbstractSession |
|+session.expire  |可配置session过期时间       |
|policies         |policy配置，如下   |

### bot类
如果需要调用自定义函数，则需要定义bot类，并继承Baidu\Iov\DmKit\Bot\Bot。

在bot子类中，可以使用函数：
* setSessionContext($key, $value)，设置session字段
* getSessionContext($key)，获取session字段
* getSlot($key)，获取Slot对象
* setState($state)，设置下一个state，设置为空则对话结束

### 垂类配置

单个垂类配置文件包括了一系列policy，每个policy字段说明如下：

| 字段                             | 类型         |说明                            |
|---------------------------------|--------------|-------------------------------|
|trigger                          |object        | 触发节点，如果一个query满足多个policy的触发条件，则优先取status匹配的policy，再根据slot取覆盖个数最多的 |
|+intent                          |string        | 触发所需NLU意图 |
|+slots                            |array          | 触发所需槽位值列表|
|+state                           |string or array  | 触发所需状态值，即上一轮对话session中保存的state字段值，初始状态为dm_init，为空表示任意状态 |
|+changed_slots                   |array          | 触发所需的当前改变的词槽 |
|params                           |array          | 变量列表 |
|+params[].name                   |string        | 变量名，后定义的变量可以使用已定义的变量进行模板填充，result节点中的值也可以使用变量进行模板填充。变量的使用格式为{%name%} |
|+params[].type                   |string        | 变量类型，可能的类型为slot_val,request_param,session_obj,func_val |
|+params[].value                  |string        | 变量定义值 |
|+params[].required               |bool          | 是否必须，如果必须的变量为空值时，该policy将不会返回结果 |
|output                           |array          | 返回结果节点，可定义多个output，最终输出会按顺序选择第一个满足assertion条件的output |
|+output[].assertion              |array          | 使用该output的前提条件列表 |
|+output[].assertion[].type       |string        | 条件类型 |
|+output[].assertion[].value      |string        | 条件值 |
|+output[].session                |object        | 需要保存的session数据，用于更新对话状态及记录上下文 |
|+output[].session.state          |string        | 更新的对话状态值 |
|+output[].session.context        |object        | 写入session的变量节点，该节点下的key+value数据会存入session，再下一轮中可以在变量定义中使用 |
|+output[].result                 |array          | 返回结果中result节点，多个result作为数组元素一起返回 |
|+output[].result[].type          |string        | result类型 |
|+output[].result[].value         |string,object | result值，如果type等于json，value可以为object            |

#### Trigger 
每个policy有且仅有一个trigger，trigger定义了policy的触发条件，有4种条件，intent，slots，changed_slots和state，四种条件中间是且的关系，必须所有条件都符合，才会进入当前policy

##### intent
intent为NLU解析结果中返回的intent，配置的intent需要等于NLU返回的intent。如果配置的intent为空，则匹配NLU结果无召回情况。
> 假设有一个音乐bot，当前正在询问用户："你想听什么歌？"，NLU可能无法解析某些歌曲名称，为了保证召回率，可以在dm模块中查询第三方资源接口，如果能找到资源，则也可以认为用户说的query是一首歌，并召回正确结果。
>- 需慎重使用，避免过召。

##### state
state为对话的当前状态，如果是第一轮对话，则当前状态为dm_init，配置的state可以为string或者array
* state为string时，对话的当前状态需要等于配置的state。
* state为array时，对话的当前状态需要包含在配置的array中

##### slots
槽位名称数组。slots为NLU结果中返回的slots，配置的所有slots都需要出现在NLU解析结果中，只验证词槽是否存在，不验证词槽的值。 如果需要验证词槽的值，需要在param中增加一个slot_val并在output中的assertion中判断。

##### changed_slots
发生变化的槽位名称数组。changed_slots为当前轮次中，词槽值发生了改变的词槽，配置的changed_slots中的所有槽位都需要发生改变，否则不触发policy。

> 当满足多个policy时，会按优先级取出一个匹配度最高的policy，具体规则如下：
>* 如果一个policy中匹配规则含有intent，另一个不含有，则取含有intent的policy
>* 如果一个policy钟匹配规则含有state，另一个不含有，则取含有state的policy
>* 如果两个policy的slots条件分别含有m和n个slot（m >= 0, n >= 0），如果m > n，则取含有m个slot的policy
>* 如果两个policy的slots条件分别含有m和n个changed_slot（m >= 0, n >= 0），如果m > n，则取含有m个changed_slot的policy

#### Param
param为参数列表，每个policy中可以定义一系列参数，在output中使用，可用于assertion，session.context及result.value，param可以为string或者object。单个policy中的参数名称不能重复。

##### 使用方法
一个policy中可以有多个param，在param中定义的参数，可以使用{%param_name%}在output中表示，会自动替换为真实值

* 当param为string时，{%param_name%}将会被替换为目标字符串 
    ```yaml
  #假设param的值为字符串test
  result:
      type: tts  
      value: {%param_name%}
   #这个会被转换成如下形式
   result:
       type: tts  
       value: test
    ```
* 当param为json object时，{%param_name%}将会被替换为object    
    ```yaml
    #假设param的值为object
    name: 忘情水
    artist: 刘德华
    url: www.baidu.com
    #result，只有当type=json时，才会递归所有子数组进行替换
    result:
        type: json  
        value: 
            music: {%param_name%}            
    #这个会被转换成如下形式
    result:
        type: json  
        value: 
            music: 
                name: 忘情水
                artist: 刘德华
                url: www.baidu.com
    ```

##### params中变量类型列表及其说明：

| type     |说明           |
|----------|--------------|
| slot_val | 从qu结果中取对应的slot值，有归一化值优先取归一化值。当对应tag值存在多个slot时，value值支持tag后按分隔符","添加下标i取对应tag的第i个值（索引从0开始） |
| ori_slot_val | 从qu结果中取对应的slot的原始值 |
| request_param | 取请求参数对应的字段 |
| session_obj | 上一轮对话session结果中objects结构体中对应的字段 |
| func_val | 调用用户定义的函数。用户定义函数需要位于前面设置的bot类中。value值为","连接的参数，其中第一个元素为函数名，第二个元素开始为函数参数 |
| qu_intent | NLU结果中的intent值 |
| session_state | 当前对话session中的state值 |
| http_request | 发送http请求，返回http_code和body |
| json_extractor | 解析数组 |

>http_request使用示例：
```json
{
  "name": "http_response",
  "type": "http_request",
  "value": "www.baidu.com",
  "options": {
    "method": "post",
    "json": {
      "query": "示例"
    },
    "header": {"Cookie": "a=a"}
  }
}
``` 

>json_extractor使用示例：
```json
{
  "name": "result",
  "type": "json_extractor",
  "value": "{%http_response%},body.results.0.data"  
}
```
用户可以自定义param handler，需要继承Baidu\Iov\DmKit\Policy\ParamHandler\AbstractHandler。


#### output
在一个policy中可以有多个output，每个output有零至多个assertion，每个assertion之间是且的关系，所有assertion都满足的时候，会输出定义的result。
> output按顺序判断，当一个output中的一个assertion满足后，不会继续判断之后的assertion

##### assertion类型说明：

| type     |说明           |
|----------|--------------|
| empty  | value值非空  |
| not_empty  | value值为空  |
| in  | value值以","切分，第一个元素在从第二个元素开始的列表中  |
| not_in | value值以","切分，第一个元素不在从第二个元素开始的列表中  |
| eq |  value值以","切分，第一个元素等于第二个元素 |
| not_eq |  value值以","切分，第一个元素不等于第二个元素 |
| gt  | value值以","切分，第一个数字大于第二个数字  |
| ge | value值以","切分，第一个数字大于等于第二个数字  |

> 逗号前后不能添加空格

用户可以自定义assertion类，需要实现Baidu\Iov\DmKit\Policy\Output\Assertion\AssertionInterface接口，并实现assert函数。type中传递assertion类名即可。

##### result
result定于了dm-kit返回的数据，可以为object或者string

* 为object时，需要包含type和value字段
   
    | type | 说明 |
    |------|-----|
    | json | value为json对象，对象中可使用param变量，变量可以为string或者json对象|
    | 其他  | value为string，string中可使用param变量，变量只能是string|
* 为string时，需要是函数名，函数需要定义在bot类中，dm-kit会调用该函数，输出函数返回结果

### 高级用法
dm-kit包含ConfLoader，Logger，Parser，Session几个组件，每种组件都做出了简单实现，可以直接使用，如果有个性化需求，可以自定义组件

#### ConfLoader
配置文件载入组件，用来载入配置文件，目前支持json和yaml格式文件。
可配置cache模块，需要实现CacheInterface接口，可以将配置文件读入缓存，避免大量磁盘I/O

```php
$loader = ['loader' => [
    'type' => 'json',    //json或者yaml,
    'cache_class' => ''  //cache类名，如不需要cache可为空,
]];
$policyManager = PolicyManagerFactory::getInstance($botId, $confPath, $loader);
```

#### Logger
使用monolog，详情见https://github.com/Seldaek/monolog

* handler名称及映射关系：

    | 名称 | handler |
    |------|-----|
    |stream | Monolog\Handler\StreamHandler|
    |group | Monolog\Handler\GroupHandler|
    |buffer | Monolog\Handler\BufferHandler|
    |deduplication | Monolog\Handler\DeduplicationHandler|
    |rotating_file | Monolog\Handler\RotatingFileHandler|
    |syslog | Monolog\Handler\SyslogHandler|
    |syslogudp | Monolog\Handler\SyslogUdpHandler|
    |null | Monolog\Handler\NullHandler|
    |test | Monolog\Handler\TestHandler|
    |gelf | Monolog\Handler\GelfHandler|
    |rollbar | Monolog\Handler\RollbarHandler|
    |flowdock | Monolog\Handler\FlowdockHandler|
    |browser_console | Monolog\Handler\BrowserConsoleHandler|
    |native_mailer | Monolog\Handler\NativeMailerHandler|
    |socket | Monolog\Handler\SocketHandler|
    |pushover | Monolog\Handler\PushoverHandler|
    |raven | Monolog\Handler\RavenHandler|
    |newrelic | Monolog\Handler\NewRelicHandler|
    |hipchat | Monolog\Handler\HipChatHandler|
    |slack | Monolog\Handler\SlackHandler|
    |slackwebhook | Monolog\Handler\SlackWebhookHandler|
    |slackbot | Monolog\Handler\SlackbotHandler|
    |cube | Monolog\Handler\CubeHandler|
    |amqp | Monolog\Handler\AmqpHandler|
    |error_log | Monolog\Handler\ErrorLogHandler|
    |loggly | Monolog\Handler\LogglyHandler|
    |logentries | Monolog\Handler\LogEntriesHandler|
    |whatfailuregroup | Monolog\Handler\WhatFailureGroupHandler|
    |fingers_crossed | Monolog\Handler\FingersCrossedHandler|
    |filter | Monolog\Handler\FilterHandler|
    |mongo | Monolog\Handler\MongoDBHandler|
    |elasticsearch | Monolog\Handler\ElasticSearchHandler|

* stream handler示例：

```yaml
#输出到std
logger:
    handler: stream
    args: ["php://stderr", "critical"]
  
#输出到文件  
logger:
    handler: stream
    args: ["/tmp/monolog.log", "critical"]
``` 

#### Parser
将语义解析结果转为QuResult对象，目前支持Unit bot api和unit hub api，
可以实现ParserInterface接口自定义Parser

#### Session
储存state和context，仅支持文件方式存储，只能用于单台构架系统，
可以实现AbstractSession自定义Session。