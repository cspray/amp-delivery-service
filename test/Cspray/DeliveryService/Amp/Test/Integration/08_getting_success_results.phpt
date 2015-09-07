--TEST--
Ensure that we can count the number of successful results
--FILE--
<?php

require_once dirname(dirname(dirname(dirname(dirname(dirname(__DIR__)))))) . '/vendor/autoload.php';

use Cspray\DeliveryService;
use Cspray\DeliveryService\Amp\ReceiptPromisorFactory;
use Cspray\DeliveryService\Amp\Mediator;

$reactor = new Amp\NativeReactor();
$promisorFactory = new ReceiptPromisorFactory();
$transmitter = new DeliveryService\StandardTransmitter($promisorFactory);
$receiver = new DeliveryService\StandardReceiver();
$mediator = new Mediator($reactor, $transmitter, $receiver);

$msg = new DeliveryService\GenericMessage('generic');

$id1 = $receiver->listen('generic', function() {
    return 1;
});
$id2 = $receiver->listen('generic', function() {
    return 2;
});
$id3 = $receiver->listen('generic', function() {
    return 3;
});
$id4 = $receiver->listen('generic', function() {
    return 4;
});

$receipt = $transmitter->send($msg);
$receipt->delivered(function(DeliveryService\DeliveryResults $results) {
    foreach ($results->getSuccessfulResults() as $key => $result) {
        echo "{$key}:{$result}\n";
    }
});

$mediator->startSendingMessages();

$reactor->tick();
$reactor->tick();
--EXPECTF--
%s:1
%s:2
%s:3
%s:4