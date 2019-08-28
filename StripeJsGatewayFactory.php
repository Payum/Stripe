<?php
namespace Payum\Stripe;

use Payum\Core\Bridge\Spl\ArrayObject;

class StripeJsGatewayFactory extends StripeCheckoutGatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'stripe_js',
            'payum.factory_title' => 'Stripe.Js',
        ]);

        if (true === $config['sca_flow']) {
            $templates = [
                'payum.template.obtain_token' => '@PayumStripe/Action/obtain_js_token_for_strong_customer_authentication.html.twig',
            ];
        } else {
            $templates = [
                'payum.template.obtain_token' => '@PayumStripe/Action/obtain_js_token.html.twig',
            ];
        }

        $config->defaults($templates);

        parent::populateConfig($config);
    }
}
