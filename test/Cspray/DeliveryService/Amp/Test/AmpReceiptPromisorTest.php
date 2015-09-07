<?php

declare(strict_types = 1);

/**
 * @license See LICENSE file in project root
 */

namespace Cspray\DeliveryService\Test\Promisor;

use Cspray\DeliveryService\Amp\ReceiptPromisor;
use Cspray\DeliveryService\Amp\Receipt;
use Cspray\DeliveryService;
use Amp;
use PHPUnit_Framework_TestCase as UnitTestCase;

class AmpReceiptPromisorTest extends UnitTestCase {

    public function testProxiesPromiseToAmpPromisor() {
        $ampPromise = $this->getMock(Amp\Promise::class);
        $ampPromisor = $this->getMock(Amp\Promisor::class);

        $ampPromisor->expects($this->once())->method('promise')->willReturn($ampPromise);

        $promisor = new ReceiptPromisor($ampPromisor);

        $receipt = $promisor->getReceipt();

        $this->assertInstanceOf(Receipt::class, $receipt);
    }

    public function testProxiesMarkDeliveredToAmpPromisor() {
        $ampPromise = $this->getMock(Amp\Promise::class);
        $ampPromisor = $this->getMock(Amp\Promisor::class);
        $deliveryResults = $this->getMock(DeliveryService\DeliveryResults::class);

        $ampPromisor->expects($this->once())->method('promise')->willReturn($ampPromise);
        $ampPromisor->expects($this->once())->method('succeed')->with($deliveryResults);

        $promisor = new ReceiptPromisor($ampPromisor);
        $promisor->markDelivered($deliveryResults);
    }

}