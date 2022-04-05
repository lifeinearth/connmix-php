<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MainTest extends TestCase
{

    public function testSend(): void
    {
        $client = \Connmix\ClientBuilder::create()
            ->setHost('127.0.0.1:6787')
            ->build();
        $client->consume(function (\Connmix\Context $ctx) {
            $msg = $ctx->message();

            var_dump($msg->type());

            $ctx->meshSend($msg->firstParam()->clientID(), sprintf("%s, me too", $msg->firstParam()->data()));
        }, function (\Exception $e) {

        }, 'foo');
    }

}
