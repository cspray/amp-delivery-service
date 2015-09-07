--TEST--
Ensure listeners can add watchers to the event loop
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

$receiver->listen('generic', function() use($reactor) {
    $reactor->immediately(function() {
        echo 'ran scheduled task';
    });
});

$transmitter->send($msg);

$mediator->startSendingMessages();

$reactor->tick(); # dequeue and schedule listeners
$reactor->tick(); # invoke listeners, schedule task
$reactor->tick(); # echo out test string
--EXPECT--
ran scheduled task