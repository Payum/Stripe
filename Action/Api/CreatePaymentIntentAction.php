<?php

namespace Payum\Stripe\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\CreatePaymentIntent;
use Stripe\Error\Base;
use Stripe\PaymentIntent;
use Stripe\Stripe;

/**
 * Class CreatePaymentIntentAction.
 *
 * @author Eric Masoero <em@studeal.fr>
 */
class CreatePaymentIntentAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait {
        setApi as _setApi;
    }

    /**
     * @deprecated BC will be removed in 2.x. Use $this->api
     *
     * @var Keys
     */
    protected $keys;

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
        /** @var $request CreatePaymentIntent */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false == ($model['payment_method'] || $model['customer'])) {
            throw new LogicException('The either payment method token or customer id has to be set.');
        }

        if (is_array($model['payment_method'])) {
            throw new LogicException('The token has already been used.');
        }

        try {
            Stripe::setApiKey($this->keys->getSecretKey());

            $charge = PaymentIntent::create(array_merge($model->toUnsafeArrayWithoutLocal(), [
                'confirmation_method' => 'manual',
                'confirm' => true,
            ]));

            $model->replace($charge->__toArray(true));
        } catch (Base $e) {
            $model->replace($e->getJsonBody());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CreatePaymentIntent &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }
}
