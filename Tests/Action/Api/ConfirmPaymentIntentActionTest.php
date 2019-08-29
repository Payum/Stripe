<?php

namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Stripe\Action\Api\ConfirmPaymentIntentAction;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\ConfirmPaymentIntent;
use PHPUnit\Framework\TestCase;

/**
 * Class ConfirmPaymentIntentActionTest.
 *
 * @author Eric Masoero <em@studeal.fr>
 */
class ConfirmPaymentIntentActionTest extends TestCase
{
    public function testItShouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass(ConfirmPaymentIntentAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    public function testItShouldImplementsApiAwareInterface()
    {
        $rc = new \ReflectionClass(ConfirmPaymentIntentAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    public function testItCouldBeConstructedWithoutAnyArguments()
    {
        new ConfirmPaymentIntentAction();
    }

    public function testItShouldAllowSetKeysAsApi()
    {
        $action = new ConfirmPaymentIntentAction();

        $action->setApi(new Keys('publishableKey', 'secretKey'));
    }

    /**
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     */
    public function testItThrowsNotSupportedApiIfNotKeysGivenAsApi()
    {
        $action = new ConfirmPaymentIntentAction();

        $action->setApi('not keys instance');
    }

    public function testItShouldSupportsConfirmPaymentIntentRequestWithArrayAccessModel()
    {
        $action = new ConfirmPaymentIntentAction();

        $this->assertTrue($action->supports(new ConfirmPaymentIntent([])));
    }

    public function testItShouldNotSupportsConfirmPaymentIntentRequestWithNotArrayAccessModel()
    {
        $action = new ConfirmPaymentIntentAction();

        $this->assertFalse($action->supports(new ConfirmPaymentIntent(new \stdClass())));
    }

    public function testItShouldNotSupportsNotConfirmPaymentIntentRequest()
    {
        $action = new ConfirmPaymentIntentAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     * @expectedExceptionMessage Action ConfirmPaymentIntentAction is not supported the request stdClass.
     */
    public function testItThrowsRequestNotSupportedIfNotSupportedGiven()
    {
        $action = new ConfirmPaymentIntentAction();

        $action->execute(new \stdClass());
    }

    /**
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The payment intent id has to be set.
     */
    public function testItThrowsIfNotPaymentIntent()
    {
        $action = new ConfirmPaymentIntentAction();

        $action->execute(new ConfirmPaymentIntent([]));
    }
}
