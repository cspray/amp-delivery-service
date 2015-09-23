<?php

declare(strict_types = 1);

/**
 * @license See LICENSE file in project root
 */

namespace Cspray\DeliveryService\Test;

use Amp;
use Cspray\DeliveryService;
use Cspray\DeliveryService\Amp\ReceiptPromisorFactory;
use Cspray\DeliveryService\Amp\Mediator;
use Cspray\DeliveryService\Exception\DeliveryServiceAlreadyRunningException;
use PHPUnit_Framework_TestCase as UnitTestCase;

class AmpMediatorTest extends UnitTestCase {

    private function getMediator() {
        $reactor = $this->getMock(Amp\Reactor::class);
        $transmitter = new DeliveryService\StandardTransmitter(new ReceiptPromisorFactory());
        $receiver = new DeliveryService\StandardReceiver();
        $mediator = new Mediator($reactor, $transmitter, $receiver);

        return [$mediator, $reactor, $transmitter, $receiver];
    }

    public function testIsSendingMessagesDefaultsToFalse() {
        list($mediator, $reactor, $transmitter, $receiver) = $this->getMediator();

        $this->assertFalse($mediator->isSendingMessages());
    }

    public function testStartSendingMarksIsSending() {
        list($mediator, $reactor, $transmitter, $receiver) = $this->getMediator();

        $mediator->startSendingMessages();

        $this->assertTrue($mediator->isSendingMessages());
    }

    public function testStartSendingAddsImmediatelyToReactor() {
        list($mediator, $reactor, $transmitter, $receiver) = $this->getMediator();

        $reactor->expects($this->once())->method('immediately')->with($this->callback(function($arg) { return $arg instanceof \Closure; }));

        $mediator->startSendingMessages();
    }

    public function testStartSendingAfterAlreadyStartedThrowsException() {
        list($mediator, $reactor, $transmitter, $receiver) = $this->getMediator();

        $reactor->expects($this->once())->method('immediately');

        $mediator->startSendingMessages();

        $exc = DeliveryServiceAlreadyRunningException::class;
        $msg = 'You have already started sending messages from this mediator';
        $this->setExpectedException($exc, $msg);

        $mediator->startSendingMessages();
    }

    public function testStopSendingMarksAsStopped() {
        list($mediator, $reactor, $transmitter, $receiver) = $this->getMediator();

        $mediator->startSendingMessages();
        $mediator->stopSendingMessages();

        $this->assertFalse($mediator->isSendingMessages());
    }

    public function testWatcherGetsCanceledFromReactor() {
        list($mediator, $reactor, $transmitter, $receiver) = $this->getMediator();

        $reactor->expects($this->once())->method('immediately')->willReturn('watcherId');
        $reactor->expects($this->once())->method('cancel')->with('watcherId');

        $mediator->startSendingMessages();
        $mediator->stopSendingMessages();
    }

    public function testStoppingMessagesNotStartedStillWorks() {
        list($mediator, $reactor, $transmitter, $receiver) = $this->getMediator();

        $mediator->stopSendingMessages();

        $this->assertFalse($mediator->isSendingMessages());
    }



}