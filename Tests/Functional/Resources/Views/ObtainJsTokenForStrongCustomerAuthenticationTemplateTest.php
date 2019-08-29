<?php

namespace Payum\Stripe\Tests\Functional\Resources\Views;

use Payum\Core\Bridge\Twig\TwigFactory;
use PHPUnit\Framework\TestCase;

/**
 * Class ObtainJsTokenForStrongCustomerAuthenticationTemplateTest.
 *
 * @author Eric Masoero <em@studeal.fr>
 */
class ObtainJsTokenForStrongCustomerAuthenticationTemplateTest extends TestCase
{
    public function testItShouldRenderObtainJsTokenTemplate()
    {
        $twig = TwigFactory::createGeneric();

        $result = $twig->render('@PayumStripe/Action/obtain_js_token_for_strong_customer_authentication.html.twig', [
            'publishable_key' => 'theKey',
        ]);

        $this->assertContains('https://js.stripe.com/v3/', $result);

        $this->assertContains('Stripe.setPublishableKey("theKey");', $result);
        $this->assertContains('Stripe.createPaymentMethod', $result);

        $this->assertContains('var token = response.paymentMethod;', $result);
    }

    public function testItShouldRenderWithGivenActionUrlIfGiven()
    {
        $twig = TwigFactory::createGeneric();

        $result = $twig->render('@PayumStripe/Action/obtain_js_token_for_strong_customer_authentication.html.twig', [
            'publishable_key' => 'theKey',
            'actionUrl' => 'awesomeUrl'
        ]);

        $this->assertContains('form action="awesomeUrl', $result);
    }
}
