--TEST--
Ensure that a Message promise is resolved with no listeners
--FILE--
<?php

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use Cspray\DeliveryService;
use Cspray\DeliveryService\Amp\ReceiptPromisorFactory;
use Cspray\DeliveryService\Amp\Mediator;
use function Amp\wait;

$reactor = new Amp\NativeReactor();
$promisorFactory = new ReceiptPromisorFactory();
$transmitter = new DeliveryService\StandardTransmitter($promisorFactory);
$receiver = new DeliveryService\StandardReceiver();
$mediator = new Mediator($reactor, $transmitter, $receiver);

$msg = new DeliveryService\GenericMessage('generic');
$promise = $transmitter->send($msg);
$promise->delivered(function() {
    echo 'resolved';
});

$mediator->startSendingMessages();

$reactor->tick();
$reactor->tick();
--EXPECT--
resolved