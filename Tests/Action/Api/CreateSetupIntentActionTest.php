<?php

namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Stripe\Action\Api\CreateSetupIntentAction;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\CreateSetupIntent;
use PHPUnit\Framework\TestCase;

/**
 * Class CreateSetupIntentActionTest.
 *
 * @author Eric Masoero <em@studeal.fr>
 */
class CreateSetupIntentActionTest extends TestCase
{
    public function testItShouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass(CreateSetupIntentAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    public function testItShouldImplementsApiAwareInterface()
    {
        $rc = new \ReflectionClass(CreateSetupIntentAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    public function testItCouldBeConstructedWithoutAnyArguments()
    {
        new CreateSetupIntentAction();
    }

    public function testItShouldAllowSetKeysAsApi()
    {
        $action = new CreateSetupIntentAction();

        $action->setApi(new Keys('publishableKey', 'secretKey'));
    }

    /**
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     */
    public function testItThrowsNotSupportedApiIfNotKeysGivenAsApi()
    {
        $action = new CreateSetupIntentAction();

        $action->setApi('not keys instance');
    }

    public function testItShouldSupportsCreateSetupIntentRequestWithArrayAccessModel()
    {
        $action = new CreateSetupIntentAction();

        $this->assertTrue($action->supports(new CreateSetupIntent([])));
    }

    public function testItShouldNotSupportsCreateSetupIntentRequestWithNotArrayAccessModel()
    {
        $action = new CreateSetupIntentAction();

        $this->assertFalse($action->supports(new CreateSetupIntent(new \stdClass())));
    }

    public function testItShouldNotSupportsNotCreateSetupIntentRequest()
    {
        $action = new CreateSetupIntentAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     * @expectedExceptionMessage Action CreateSetupIntentAction is not supported the request stdClass.
     */
    public function testItThrowsRequestNotSupportedIfNotSupportedGiven()
    {
        $action = new CreateSetupIntentAction();

        $action->execute(new \stdClass());
    }
}
