<?php

declare(strict_types = 1);

/**
 * @license See LICENSE file in project root
 */

namespace Cspray\DeliveryService\Amp;

use Cspray\DeliveryService;
use Amp;

class Receipt implements DeliveryService\Receipt {

    private $promise;

    public function __construct(Amp\Promise $promise) {
        $this->promise = $promise;
    }

    /**
     * Invokes a callable when the Message for this Receipt has been delivered to
     * all listeners.
     *
     * function(DeliveryResults $results) {
     *
     * }
     *
     * @param callable $cb
     * @return self
     */
    public function delivered(callable $cb) {
        $this->promise->when(function($error = null, $result = null) use($cb) {
            $cb($result);
        });
    }

}