<?php

namespace Payum\Stripe\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Stripe\Request\Api\ConfirmPaymentIntent;
use Payum\Stripe\Request\Confirm;
use Payum\Stripe\Request\RequireConfirmation;

/**
 * Class ConfirmAction.
 *
 * @author Eric Masoero <em@studeal.fr>
 */
class ConfirmAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param Confirm $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $model['payment_intent']) {
            $confirmation = new RequireConfirmation($model);
            $confirmation->setModel($model);

            $this->gateway->execute($confirmation);
        }

        $this->gateway->execute(new ConfirmPaymentIntent($model));
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Confirm &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }
}
