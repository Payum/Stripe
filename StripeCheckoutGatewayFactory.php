<?php
namespace Payum\Stripe;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\GatewayFactory;
use Payum\Stripe\Action\Api\ConfirmPaymentIntentAction;
use Payum\Stripe\Action\Api\CreateChargeAction;
use Payum\Stripe\Action\Api\CreateCustomerAction;
use Payum\Stripe\Action\Api\CreatePaymentIntentAction;
use Payum\Stripe\Action\Api\CreatePlanAction;
use Payum\Stripe\Action\Api\CreateSubscriptionAction;
use Payum\Stripe\Action\Api\CreateTokenAction;
use Payum\Stripe\Action\Api\ObtainTokenAction;
use Payum\Stripe\Action\Api\ObtainTokenForStrongCustomerAuthenticationAction;
use Payum\Stripe\Action\CaptureAction;
use Payum\Stripe\Action\ConfirmAction;
use Payum\Stripe\Action\ConvertPaymentAction;
use Payum\Stripe\Action\GetCreditCardTokenAction;
use Payum\Stripe\Action\RequireConfirmationAction;
use Payum\Stripe\Action\StrongCustomerAuthenticationCaptureAction;
use Payum\Stripe\Extension\CreateCustomerExtension;
use Payum\Stripe\Action\StatusAction;
use Stripe\Stripe;

class StripeCheckoutGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        if (false == class_exists(Stripe::class)) {
            throw new LogicException('You must install "stripe/stripe-php:^3|^4|^5|^6" library.');
        }

        $config->defaults([
            'payum.factory_name' => 'stripe_checkout',
            'payum.factory_title' => 'Stripe Checkout',

            'payum.template.obtain_token' => '@PayumStripe/Action/obtain_checkout_token.html.twig',

            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.get_credit_card_token' => new GetCreditCardTokenAction(),
            'payum.action.create_customer' => new CreateCustomerAction(),
            'payum.action.create_plan' => new CreatePlanAction(),
            'payum.action.create_token' => new CreateTokenAction(),
            'payum.action.create_subscription' => new CreateSubscriptionAction(),

            'payum.extension.create_customer' => new CreateCustomerExtension(),
        ]);

        if (true === $config['sca_flow']) {
            $actions = [
                'payum.action.capture' => new StrongCustomerAuthenticationCaptureAction(),
                'payum.action.obtain_token' => function (ArrayObject $config) {
                    $template = $config['payum.template.obtain_token'];

                    return new ObtainTokenForStrongCustomerAuthenticationAction($template);
                },
                'payum.action.create_charge' => new CreatePaymentIntentAction(),

                'payum.action.confirm_payment' => new ConfirmAction(),
                'payum.action.require_confirmation' => function (ArrayObject $config) {
                    $template = $config['payum.template.require_confirmation'];

                    return new RequireConfirmationAction($template);
                },
                'payum.action.confirm_payment_intent' => new ConfirmPaymentIntentAction(),

                'payum.template.require_confirmation' => '@PayumStripe/Action/require_confirmation.html.twig',
            ];
        } else {
            $actions = [
                'payum.action.capture' => new CaptureAction(),
                'payum.action.obtain_token' => function (ArrayObject $config) {
                    $template = $config['payum.template.obtain_token'];

                    return new ObtainTokenAction($template);
                },
                'payum.action.create_charge' => new CreateChargeAction(),
            ];
        }

        $config->defaults($actions);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = [
                'publishable_key' => '',
                'secret_key' => '',
                'sca_flow' => false,
            ];
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ['publishable_key', 'secret_key'];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Keys($config['publishable_key'], $config['secret_key']);
            };
        }

        $config['payum.paths'] = array_replace([
            'PayumStripe' => __DIR__.'/Resources/views',
        ], $config['payum.paths'] ?: []);
    }
}
