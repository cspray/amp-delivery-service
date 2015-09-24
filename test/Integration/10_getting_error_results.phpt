--TEST--
Ensure that we can count the number of successful results
--FILE--
<?php

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use Cspray\DeliveryService;
use Cspray\DeliveryService\Amp\ReceiptPromisorFactory;
use Cspray\DeliveryService\Amp\Mediator;

$reactor = new Amp\NativeReactor();
$promisorFactory = new ReceiptPromisorFactory();
$transmitter = new DeliveryService\StandardTransmitter($promisorFactory);
$receiver = new DeliveryService\StandardReceiver();
$mediator = new Mediator($reactor, $transmitter, $receiver);

$receiver->listen('generic', function() { return 1; });
$receiver->listen('generic', function() { throw new Exception('listener 2'); });
$receiver->listen('generic', function() { return 3; });
$receiver->listen('generic', function() { return 4; });

$receipt = $transmitter->send('generic');
$receipt->delivered(function(DeliveryService\DeliveryResults $results) {
    foreach ($results->getFailureResults() as $result) {
        echo $result->getMessage();
    }
});

$mediator->startSendingMessages();

$reactor->tick();
$reactor->tick();
--EXPECTF--
listener 2