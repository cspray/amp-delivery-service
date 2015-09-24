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

$receiver->listen('generic', function() {});
$receiver->listen('generic', function() { throw new Exception; });
$receiver->listen('generic', function() {});
$receiver->listen('generic', function() {});

$receipt = $transmitter->send('generic');
$receipt->delivered(function(DeliveryService\DeliveryResults $results) {
    echo $results->getNumberListeners();
    echo count($results->getSuccessfulResults());
    echo count($results->getFailureResults());
});

$mediator->startSendingMessages();

$reactor->tick();
$reactor->tick();
--EXPECT--
431