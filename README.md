## CONNMIX PHP client

通过该客户端，可以使用 PHP 语言来消费 connmix 内存队列内的业务消息，执行对应的业务逻辑。

## 快速上手

### 安装

```
composer require connmix/connmix
```

### 创建客户端

- 该客户端为异步模式
- `$onFulfilled` 闭包内处理业务逻辑
- `$onRejected` 闭包内处理网络异常
- 可以在 `Laravel`、`ThinkPHP` 等任意框架中使用

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

## License

Apache License Version 2.0, http://www.apache.org/licenses/
