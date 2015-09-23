--TEST--
Ensure that if a listener returns a Promise its resolved
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
$deferred = new Amp\Deferred();

$receiver->listen('generic', function() { return 1; });
$receiver->listen('generic', function() use($deferred) {
    return $deferred->promise();
});
$receiver->listen('generic', function() { return 3; });
$receiver->listen('generic', function() { return 4; });

$receipt = $transmitter->send($msg);
$receipt->delivered(function(DeliveryService\DeliveryResults $results) {
    foreach ($results->getSuccessfulResults() as $result) {
        echo $result;
    }
});

$mediator->startSendingMessages();

$reactor->tick();
$reactor->tick();
$reactor->tick();
$reactor->tick();
$reactor->tick();

$deferred->succeed(9);
--EXPECTF--
1349