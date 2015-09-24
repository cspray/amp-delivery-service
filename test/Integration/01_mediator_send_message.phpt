--TEST--
Ensure the Mediator dispatches any queued messages
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

$receiver->listen('generic', function($messageType, $payload) {
    echo $messageType;
});

$transmitter->send('generic');

$mediator->startSendingMessages();
$reactor->tick();
$reactor->tick();
--EXPECT--
generic