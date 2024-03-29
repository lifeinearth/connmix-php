<?php

require __DIR__ . '/../vendor/autoload.php';

$client = \Connmix\ClientBuilder::create()
    ->setHost('127.0.0.1:6787')
    ->build();
$onFulfilled = function (\Connmix\AsyncNodeInterface $node) {
    $message = $node->message();
    switch ($message->type()) {
        case "consume":
            $clientID = $message->clientID();
            $data = $message->data();
            $node->meshSend($clientID, sprintf("received: %s", $data['frame']['data'] ?? ''));
            break;
        case "result":
            $success = $message->success();
            $fail = $message->fail();
            $total = $message->total();
            break;
        case "error":
            $error = $message->error();
            break;
        default:
            $payload = $message->rawMessage();
    }
};
$onRejected = function (\Throwable $e) {
    // handle error
    var_dump($e->getMessage());
};
$client->consume('foo')->then($onFulfilled, $onRejected);
