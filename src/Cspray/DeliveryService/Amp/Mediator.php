<?php

declare(strict_types = 1);

/**
 * @license See LICENSE file in project root
 */

namespace Cspray\DeliveryService\Amp;

use Cspray\DeliveryService;
use Cspray\DeliveryService\Exception\DeliveryServiceAlreadyRunningException;
use Amp;

class Mediator implements DeliveryService\Mediator {

    private $reactor;
    private $transmitter;
    private $receiver;
    private $isSending = false;
    private $watcherId;

    public function __construct(Amp\Reactor $reactor, DeliveryService\Transmitter $transmitter, DeliveryService\Receiver $receiver) {
        $this->reactor = $reactor;
        $this->transmitter = $transmitter;
        $this->receiver = $receiver;
    }

    public function isSendingMessages() : bool {
        return $this->isSending;
    }

    public function startSendingMessages() {
        if ($this->isSending) {
            throw new DeliveryServiceAlreadyRunningException('You have already started sending messages from this mediator');
        }

        $cb = $this->getDispatchCallback();

        $this->watcherId = $this->reactor->immediately($cb);
        $this->isSending = true;
    }

    public function stopSendingMessages() {
        $this->reactor->cancel($this->watcherId);
        $this->isSending = false;
        $this->watcherId = null;
    }

    private function dispatchMessage() {
        if ($this->transmitter->getMessageQueue()->hasMessages()) {
            $messageReceiptPromisor = $this->transmitter->getMessageQueue()->dequeue();
            $msg = $messageReceiptPromisor->getMessage();
            $promisor = $messageReceiptPromisor->getReceiptPromisor();
            $listenerPromises = [];
            foreach ($this->receiver->getListeners($msg->getType()) as $key => $listener) {
                $listenerPromisor = $this->getAmpPromisor();
                $listenerPromises[$key] = $listenerPromisor->promise();
                $this->reactor->immediately(function() use($listener, $msg, $listenerPromisor) {
                    try {
                        $listenerPromisor->succeed($listener($msg));
                    } catch (\Exception $exception) {
                        $listenerPromisor->fail($exception);
                    }
                });
            }

            $this->resolve($promisor, $listenerPromises);
        }
    }

    private function getDispatchCallback() {
        $dispatchCb = function() {
            $this->dispatchMessage();
            $cb = $this->getDispatchCallback();
            $this->watcherId = $this->reactor->immediately($cb);
        };
        return $dispatchCb->bindTo($this, get_class($this));
    }

    private function getAmpPromisor() : Amp\Promisor {
        return new class implements Amp\Promisor {
            use Amp\PrivatePromisor;
        };
    }

    private function resolve(DeliveryService\ReceiptPromisor $promisor, array $promises) {
        if (empty($promises)) {
            $deliveryResults = new DeliveryService\StandardDeliveryResults();
            $promisor->markDelivered($deliveryResults);
            return;
        }

        $data = new \stdClass();
        $data->remaining = count($promises);
        $data->success = [];
        $data->failure = [];
        $data->promisor = $this->getAmpPromisor();

        $resolve = function($error, $result, $cbData) use($promisor) {
            list($data, $key) = $cbData;
            if (empty($data->remaining)) {
                return;
            }

            if ($error) {
                $data->failure[$key ] = $error;
            } else {
                $data->success[$key] = $result;
            }


            if (--$data->remaining === 0) {
                $deliveryResults = new DeliveryService\StandardDeliveryResults($data->success, $data->failure);
                $promisor->markDelivered($deliveryResults);
            }
        };

        foreach ($promises as $key => $promise) {
            $promise->when($resolve, [$data, $key]);
        }
    }

}