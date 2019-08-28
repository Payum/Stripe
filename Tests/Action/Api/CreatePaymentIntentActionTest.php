<?php

namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Stripe\Action\Api\CreatePaymentIntentAction;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\CreatePaymentIntent;
use PHPUnit\Framework\TestCase;

/**
 * Class CreatePaymentIntentActionTest.
 *
 * @author Eric Masoero <em@studeal.fr>
 */
class CreatePaymentIntentActionTest extends TestCase
{
    public function testItShouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass(CreatePaymentIntentAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    public function testItShouldImplementsApiAwareInterface()
    {
        $rc = new \ReflectionClass(CreatePaymentIntentAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    public function testItCouldBeConstructedWithoutAnyArguments()
    {
        new CreatePaymentIntentAction();
    }

    public function testItShouldAllowSetKeysAsApi()
    {
        $action = new CreatePaymentIntentAction();

        $action->setApi(new Keys('publishableKey', 'secretKey'));
    }

    /**
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     */
    public function testItThrowsNotSupportedApiIfNotKeysGivenAsApi()
    {
        $action = new CreatePaymentIntentAction();

        $action->setApi('not keys instance');
    }

    public function testItShouldSupportsCreatePaymentIntentRequestWithArrayAccessModel()
    {
        $action = new CreatePaymentIntentAction();

        $this->assertTrue($action->supports(new CreatePaymentIntent([])));
    }

    public function testItShouldNotSupportsCreatePaymentIntentRequestWithNotArrayAccessModel()
    {
        $action = new CreatePaymentIntentAction();

        $this->assertFalse($action->supports(new CreatePaymentIntent(new \stdClass())));
    }

    public function testItShouldNotSupportsNotCreatePaymentIntentRequest()
    {
        $action = new CreatePaymentIntentAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     * @expectedExceptionMessage Action CreatePaymentIntentAction is not supported the request stdClass.
     */
    public function testItThrowsRequestNotSupportedIfNotSupportedGiven()
    {
        $action = new CreatePaymentIntentAction();

        $action->execute(new \stdClass());
    }

    /**
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The either payment method token or customer id has to be set.
     */
    public function testItThrowsIfNotPaymentMethodNorCustomer()
    {
        $action = new CreatePaymentIntentAction();

        $action->execute(new CreatePaymentIntent([]));
    }

    /**
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The token has already been used.
     */
    public function testItThrowsIfPaymentMethodIsArray()
    {
        $action = new CreatePaymentIntentAction();

        $action->execute(new CreatePaymentIntent(['payment_method' => ['something']]));
    }
}
