<?php

namespace Payum\Stripe\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;
use Payum\Stripe\Constants;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\RequireConfirmation;

/**
 * Class RequireConfirmationAction.
 *
 * @author Eric Masoero <em@studeal.fr>
 */
class RequireConfirmationAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use ApiAwareTrait {
        setApi as _setApi;
    }
    use GatewayAwareTrait;

    /**
     * @var string
     */
    protected $templateName;

    /**
     * @deprecated BC will be removed in 2.x. Use $this->api
     *
     * @var Keys
     */
    protected $keys;

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
        /** @var $request RequireConfirmation */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (Constants::STATUS_REQUIRES_PAYMENT_METHOD != $model['status']
            && (Constants::STATUS_REQUIRES_ACTION != $model['status']
            || !isset($model['next_action'], $model['next_action']['type'])
            || !Constants::NEXT_ACTION_TYPE == $model['next_action']['type'])
        ) {
            throw new LogicException('The payment does not need further confirmation');
        }

        $getHttpRequest = new GetHttpRequest();
        $this->gateway->execute($getHttpRequest);
        if ($getHttpRequest->method == 'POST' && isset($getHttpRequest->request['stripeToken'])) {
            $model['payment_intent'] = $getHttpRequest->request['stripeToken'];

            return;
        }

        $this->gateway->execute($renderTemplate = new RenderTemplate($this->templateName, array(
            'model' => $model,
            'publishable_key' => $this->keys->getPublishableKey(),
            'payment_intent_client_secret' => $model['client_secret'],
        )));

        throw new HttpResponse($renderTemplate->getResult());
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof RequireConfirmation &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }
}
