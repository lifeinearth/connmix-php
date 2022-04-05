# CONNMIX PHP client

通过该客户端，可以使用 PHP 来消费 connmix 内存队列内的用户消息，执行业务逻辑后响应到对应的用户。

## 快速上手

### 安装

```
composer require connmix/connmix
```

### 创建客户端

该客户端为异步模式

- `$onFulfilled` 闭包内处理业务逻辑。
- `$onRejected` 闭包内处理网络异常。
- 可以在 `Laravel`、`ThinkPHP` 等任意框架中使用。
- 使用 `meshSend`、`meshPublish` 方法给客户端响应数据。

```php
$client = \Connmix\ClientBuilder::create()
    ->setHost('127.0.0.1:6787')
    ->build();
$onFulfilled = function (\Connmix\Context $ctx) {
    $message = $ctx->message();
    switch ($message->type()) {
        case "pop":
            $clientID = $message->clientID();
            $data = $message->data();
            // do something
            $ctx->meshSend($clientID, sprintf("received: %s", $data['frame']['data'] ?? ''));
            break;
        case "result":
            $success = $message->success();
            $fail = $message->fail();
            $total = $message->total();
            break;
        case "error":
            $error = $message->error();
            break;
        case "unknown":
            $payload = $message->rawMessage()->getPayload();
            break;
    }
};
$onRejected = function (\Exception $e) {
    // handle error
};
$client->consume('foo')->then($onFulfilled, $onRejected);
```

## 设置上下文



## 订阅频道

通过给某个连接订阅频道，我们可以给这些连接分组，比如：我有手机、电脑的2个连接，在通过授权验证后，我们可以都订阅 `user_10001` 频道，这样我们给该频道发送消息时就可以达到两个设备都可以收到消息的效果。



## License

Apache License Version 2.0, http://www.apache.org/licenses/
