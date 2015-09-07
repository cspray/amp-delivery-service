# DeliveryService

An implementation of [DeliveryService](https://github.com/cspray/delivery-service) using the [Amp]() event loop reactor. 
It is expected before uing this library you understand how these 2 dependencies operate.

## DeliveryService Hello World

Here we take a look at the official Hello World example for DeliveryService implementations. This is 
the bare minimum 

```
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Cspray\DeliveryService;
use Cspray\DeliveryService\Amp\ReceiptPromisorFactory;
use Cspray\DeliveryService\Amp\Mediator;

$reactor = new Amp\NativeReactor();
$promisorFactory = new ReceiptPromisorFactory();
$transmitter = new DeliveryService\Transmitter($promisorFactory);
$receiver = new DeliveryService\Receiver();
$mediator = new Mediator($reactor, $transmitter, $receiver);

$message = new DeliveryService\GenericMessage('foobar', 'the payload');
$messagePromise = $transmitter->send($message);
$messagePromise->delivered(function() {
    echo "after listeners\n";
});

$receiver->listen('foobar', function(DeliveryService\Message $message) {
    echo "{$message->getType()}\n";
    echo "{$message->getPayload()}\n";
});

$mediator->startSendingMessages();

$reactor->tick();
$reactor->tick();

//--EXPECT--
foobar
the payload
after listeners

```

## Two-tick delivery

The 2 ticks in the above example is not a mistake. We intentionally invoke message listeners
the tick *after* it gets removed from the queue. This is done so that listeners run within 
the event loop and are invoked concurrently.


```
--------|------------------|------------------|---------------|---
 dequeue message     invoke listeners   invoke listeners      .
 queue listeners     dequeue message    dequeue message       .
                     queue listeners    queue listeners       .
```