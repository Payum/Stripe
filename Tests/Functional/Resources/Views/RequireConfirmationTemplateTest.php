<?php

namespace Payum\Stripe\Tests\Functional\Resources\Views;

use Payum\Core\Bridge\Twig\TwigFactory;
use PHPUnit\Framework\TestCase;

/**
 * Class RequireConfirmationTemplateTest.
 *
 * @author Eric Masoero <em@studeal.fr>
 */
class RequireConfirmationTemplateTest extends TestCase
{
    public function testItShouldRenderRequireConfirmationTemplate()
    {
        $twig = TwigFactory::createGeneric();

        $result = $twig->render('@PayumStripe/Action/require_confirmation.html.twig', [
            'publishable_key' => 'theKey',
            'payment_intent_client_secret' => 'intentId',
        ]);

        $this->assertContains('https://js.stripe.com/v3/', $result);

        $this->assertContains('Stripe.setPublishableKey("theKey");', $result);
        $this->assertContains('Stripe.handleCardAction(\'intentId\'', $result);

        $this->assertContains('var token = response.paymentIntent.id;', $result);
    }

    public function testItShouldRenderWithGivenActionUrlIfGiven()
    {
        $twig = TwigFactory::createGeneric();

        $result = $twig->render('@PayumStripe/Action/require_confirmation.html.twig', [
            'publishable_key' => 'theKey',
            'payment_intent_client_secret' => 'intentId',
            'actionUrl' => 'awesomeUrl'
        ]);

        $this->assertContains('form action="awesomeUrl', $result);
    }
}
