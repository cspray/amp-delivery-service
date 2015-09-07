--TEST--

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

$first = new DeliveryService\GenericMessage('generic', 1);
$second = new DeliveryService\GenericMessage('generic', 2);

$listener = function(DeliveryService\Message $message) {
    $type = $message->getType();
    $payload = $message->getPayload();
    echo "{$type}{$payload}\n";
};
$receiver->listen('generic', $listener);

$transmitter->send($first);
$transmitter->send($second);

$mediator->startSendingMessages();

$reactor->tick(); # dequeue first message and schedule listener callbacks
$reactor->tick(); # invoke first listener callbacks, dequeue next message and schedule second listener callbacks
$reactor->tick(); #
--EXPECT--
generic1
generic2
