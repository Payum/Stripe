<?php

namespace Payum\Stripe\Action\CheckoutServer;

use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Sync;
use Payum\Stripe\Request\Api\CreateSession;

class CaptureAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        /* @var $request Capture */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $model['id']) {
            $model['success_url'] = $request->getToken()->getTargetUrl();
            $model['cancel_url'] = $request->getToken()->getTargetUrl();

            $this->gateway->execute(new CreateSession($model));
        }

        $this->gateway->execute(new Sync($model));
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
