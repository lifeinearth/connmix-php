<?php

require __DIR__ . '/../vendor/autoload.php';

$client = \Connmix\ClientBuilder::create()
    ->setHost('127.0.0.1:6787')
    ->build();
$onFulfilled = function (\Connmix\Context $ctx) {
    $message = $ctx->message();
    switch ($message->type()) {
        case "pop":
            $clientID = $message->firstParam()->clientID();
            $data = $message->firstParam()->data();
            $ctx->meshSend($clientID, sprintf("%s, me too", $data['frame']['data'] ?? ''));
            break;
        case "result":
            $success = $message->firstResult()->success();
            $fail = $message->firstResult()->fail();
            $total = $message->firstResult()->total();
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
