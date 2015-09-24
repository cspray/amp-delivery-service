--TEST--

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

$listener = function($type, $payload) {
    $payload = $payload[0];
    echo "{$type}{$payload}\n";
};
$receiver->listen('generic', $listener);

$transmitter->send('generic', [1]);
$transmitter->send('generic', [2]);

$mediator->startSendingMessages();

$reactor->tick(); # dequeue first message and schedule listener callbacks
$reactor->tick(); # invoke first listener callbacks, dequeue next message and schedule second listener callbacks
$reactor->tick(); #
--EXPECT--
generic1
generic2
