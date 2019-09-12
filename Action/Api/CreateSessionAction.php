<?php

namespace Payum\Stripe\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\RenderTemplate;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\CreateSession;
use Stripe\Checkout\Session;
use Stripe\Error;
use Stripe\Stripe;

class CreateSessionAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
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
     * @var string
     */
    protected $templateName;

    /**
     * @param string $templateName
     */
    public function __construct($templateName)
    {
        $this->templateName = $templateName;

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

        try {
            Stripe::setApiKey($this->keys->getSecretKey());

            $session = Session::create($model->toUnsafeArrayWithoutLocal());

            $model->replace($session->__toArray(true));
        } catch (Error\Base $e) {
            $model->replace($e->getJsonBody());

            return;
        }

        $this->gateway->execute($renderTemplate = new RenderTemplate($this->templateName, [
            'publishable_key' => $this->keys->getPublishableKey(),
            'session_id' => $model['id'],
        ]));

        throw new HttpResponse($renderTemplate->getResult());
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CreateSession &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
