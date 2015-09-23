--TEST--
Ensure that Message promise only resolves after listeners invoked
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

$msg = new DeliveryService\GenericMessage('generic');

$receiver->listen('generic', function() {
    echo "listener\n";
});

$promise = $transmitter->send($msg);
$promise->delivered(function() {
    echo "resolved\n";
});

$mediator->startSendingMessages();

$reactor->tick();
$reactor->tick();
--EXPECT--
listener
resolved