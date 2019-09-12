<?php

namespace Payum\Stripe\Action\CheckoutServer;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use Payum\Stripe\Constants;
use Stripe\PaymentIntent;
use Stripe\Refund;

class StatusAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['error']) {
            $request->markFailed();

            return;
        }

        if (false == $model['status']) {
            $request->markNew();

            return;
        }

        if ($model['object'] === PaymentIntent::OBJECT_NAME && Constants::STATUS_PROCESSING === $model['status']) {
            $request->markPending();

            return;
        }

        if ($model['object'] === Refund::OBJECT_NAME && Constants::STATUS_SUCCEEDED === $model['status']) {
            $request->markRefunded();

            return;
        }

        if ($model['object'] === PaymentIntent::OBJECT_NAME && Constants::STATUS_CANCELED == $model['status']) {
            $request->markCanceled();

            return;
        }

        if ($model['object'] === PaymentIntent::OBJECT_NAME && Constants::STATUS_SUCCEEDED === $model['status']) {
            $request->markCaptured();

            return;
        }

        if ($model['object'] === PaymentIntent::OBJECT_NAME && Constants::STATUS_REQUIRES_PAYMENT_METHOD == $model['status']) {
            $request->markAuthorized();

            return;
        }

        $request->markUnknown();
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
