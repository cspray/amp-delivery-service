<?php

declare(strict_types = 1);

/**
 * @license See LICENSE file in project root
 */

namespace Cspray\DeliveryService\Amp;

use Cspray\DeliveryService;

class ReceiptPromisorFactory implements DeliveryService\ReceiptPromisorFactory {

    public function create() : DeliveryService\ReceiptPromisor {
        return new ReceiptPromisor();
    }

}