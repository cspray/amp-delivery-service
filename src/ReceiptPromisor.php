<?php

declare(strict_types = 1);

/**
 * @license See LICENSE file in project root
 */

namespace Cspray\DeliveryService\Amp;

use Cspray\DeliveryService;
use Amp;

class ReceiptPromisor implements DeliveryService\ReceiptPromisor {

    private $promisor;
    private $receipt;

    public function __construct(Amp\Promisor $promisor = null) {
        $this->promisor = $promisor ?? $this->getAmpPromisor();
        $this->receipt = new Receipt($this->promisor->promise());
    }

    public function getReceipt() : DeliveryService\Receipt {
        return $this->receipt;
    }

    public function markDelivered(DeliveryService\DeliveryResults $results) {
        $this->promisor->succeed($results);
    }

    private function getAmpPromisor() : Amp\Promisor {
        return new class implements Amp\Promisor {
            use Amp\PrivatePromisor;
        };
    }


}