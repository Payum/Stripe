<?php

namespace Payum\Stripe\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Stripe\Request\Api\CreatePaymentIntent;
use Payum\Stripe\Request\Api\ObtainTokenForStrongCustomerAuthentication;

/**
 * Class StrongCustomerAuthenticationCaptureAction.
 *
 * @author Eric Masoero <em@studeal.fr>
 */
class StrongCustomerAuthenticationCaptureAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['status']) {
            return;
        }

        if ($model['customer']) {
        } else {
            if (false == $model['payment_method']) {
                $obtainToken = new ObtainTokenForStrongCustomerAuthentication($request->getToken());
                $obtainToken->setModel($model);

                $this->gateway->execute($obtainToken);
            }
        }

        $this->gateway->execute(new CreatePaymentIntent($model));
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }
}
