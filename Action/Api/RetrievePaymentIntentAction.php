<?php

namespace Payum\Stripe\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\RetrievePaymentIntent;
use Stripe\Error;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class RetrievePaymentIntentAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use ApiAwareTrait {
        setApi as _setApi;
    }
    use GatewayAwareTrait;

    /**
     * @deprecated BC will be removed in 2.x. Use $this->api
     *
     * @var Keys
     */
    protected $keys;

    /**
     * @param string $templateName
     */
    public function __construct()
    {
        $this->apiClass = Keys::class;
    }

    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        $this->_setApi($api);

        // BC. will be removed in 2.x
        $this->keys = $this->api;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request CreateSession */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (empty($model['payment_intent'])) {
            throw new LogicException('PaymentIntent ID has to be provided.');
        }

        try {
            Stripe::setApiKey($this->keys->getSecretKey());

            $paymentIntent = PaymentIntent::retrieve($model['payment_intent']);
            $model->replace($paymentIntent->__toArray(true));
        } catch (Error\Base $e) {
            $model->replace($e->getJsonBody());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof RetrievePaymentIntent &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}

