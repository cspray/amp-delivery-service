--TEST--
Ensures that you can start and stop sending messages at will
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
$third = new DeliveryService\GenericMessage('generic', 3);

$listener = function(DeliveryService\Message $message) {
    $type = $message->getType();
    $payload = $message->getPayload();
    echo "{$type}{$payload}\n";
};
$receiver->listen('generic', $listener);

$transmitter->send($first);
$transmitter->send($second);
$transmitter->send($third);

$mediator->startSendingMessages();

$reactor->tick();
$mediator->stopSendingMessages();

$reactor->tick();

echo "stopped\n";

$reactor->tick();
$reactor->tick();
$mediator->startSendingMessages();

echo "started\n";

$reactor->tick();
$reactor->tick();
$reactor->tick();
--EXPECT--
generic1
stopped
started
generic2
generic3