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

$receiver->listen('generic', function() {
    echo "1\n";
});
$receiver->listen('generic', function() {
    echo "2\n";
});
$receiver->listen('generic', function() {
    echo "3\n";
});
$receiver->listen('generic', function() {
    echo "4\n";
});

$receipt = $transmitter->send('generic');
$receipt->delivered(function(DeliveryService\DeliveryResults $results) {
    echo $results->getNumberListeners();
});

$mediator->startSendingMessages();

$reactor->tick();
$reactor->tick();
--EXPECT--
1
2
3
4
4