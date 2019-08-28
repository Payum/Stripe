<?php

namespace Payum\Stripe\Tests\Action;

use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;
use Payum\Stripe\Action\RequireConfirmationAction;
use Payum\Stripe\Constants;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\RequireConfirmation;
use PHPUnit\Framework\TestCase;

/**
 * Class RequireConfirmationActionTest.
 *
 * @author Eric Masoero <em@studeal.fr>
 */
class RequireConfirmationActionTest extends TestCase
{
    protected $requestClass = RequireConfirmation::class;

    protected $actionClass = RequireConfirmationAction::class;

    public function testItShouldImplementsGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(RequireConfirmationAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testItShouldImplementsApiAwareInterface()
    {
        $rc = new \ReflectionClass(RequireConfirmationAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    public function testItCouldBeConstructedWithTemplateAsFirstArgument()
    {
        new RequireConfirmationAction('aTemplateName');
    }

    public function testItShouldAllowsSetKeysAsApi()
    {
        $action = new RequireConfirmationAction('aTemplateName');

        $action->setApi(new Keys('publishableKey', 'secretKey'));
    }

    /**
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     */
    public function testItThrowsNotSupportedApiIfNotKeysGivenAsApi()
    {
        $action = new RequireConfirmationAction('aTemplateName');

        $action->setApi('not keys instance');
    }

    public function testItShouldSupportsRequireConfirmationRequestWithArrayAccessModel()
    {
        $action = new RequireConfirmationAction('aTemplateName');

        $this->assertTrue($action->supports(new RequireConfirmation([])));
    }

    public function testItShouldNotSupportRequireConfirmationRequestWithNotArrayAccessModel()
    {
        $action = new RequireConfirmationAction('aTemplateName');

        $this->assertFalse($action->supports(new RequireConfirmation(new \stdClass())));
    }

    public function testItShouldNotSupportNotRequireConfirmationRequest()
    {
        $action = new RequireConfirmationAction('aTemplateName');

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     * @expectedExceptionMessage Action RequireConfirmationAction is not supported the request stdClass.
     */
    public function testItThrowsRequestNotSupportedIfNotSupportedGiven()
    {
        $action = new RequireConfirmationAction('aTemplateName');

        $action->execute(new \stdClass());
    }

    /**
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The payment does not need further confirmation
     */
    public function testItThrowsIfPaymentDoesNotNeedConfirmation()
    {
        $action = new RequireConfirmationAction('aTemplateName');

        $action->execute(new RequireConfirmation([
            'status' => Constants::STATUS_SUCCEEDED,
        ]));
    }

    public function testItShouldRenderExpectedTemplateIfHttpRequestNotPOST()
    {
        $model = [
            'status' => Constants::STATUS_REQUIRES_PAYMENT_METHOD,
        ];
        $templateName = 'theTemplateName';
        $publishableKey = 'thePubKey';

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->will($this->returnCallback(function (GetHttpRequest $request) {
                $request->method = 'GET';
            }))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(RenderTemplate::class))
            ->will($this->returnCallback(function (RenderTemplate $request) use ($templateName, $publishableKey, $model) {
                $this->assertEquals($templateName, $request->getTemplateName());

                $context = $request->getParameters();
                $this->assertArrayHasKey('model', $context);
                $this->assertArrayHasKey('publishable_key', $context);
                $this->assertEquals($publishableKey, $context['publishable_key']);

                $request->setResult('theContent');
            }))
        ;

        $action = new RequireConfirmationAction($templateName);
        $action->setGateway($gatewayMock);
        $action->setApi(new Keys($publishableKey, 'secretKey'));

        try {
            $action->execute(new RequireConfirmation($model));
        } catch (HttpResponse $reply) {
            $this->assertEquals('theContent', $reply->getContent());

            return;
        }

        $this->fail('HttpResponse reply was expected to be thrown.');
    }

    public function testItShouldRenderTemplateIfHttpRequestPOSTButNotContainStripeToken()
    {
        $model = [
            'status' => Constants::STATUS_REQUIRES_PAYMENT_METHOD,
        ];
        $templateName = 'aTemplateName';
        $publishableKey = 'aPubKey';

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->will($this->returnCallback(function (GetHttpRequest $request) {
                $request->method = 'POST';
            }))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(RenderTemplate::class))
        ;

        $action = new RequireConfirmationAction($templateName);
        $action->setGateway($gatewayMock);
        $action->setApi(new Keys($publishableKey, 'secretKey'));

        try {
            $action->execute(new RequireConfirmation($model));
        } catch (HttpResponse $reply) {
            return;
        }

        $this->fail('HttpResponse reply was expected to be thrown.');
    }

    public function testItShouldSetTokenFromHttpRequestToObtainTokenRequestOnPOST()
    {
        $model = [
            'status' => Constants::STATUS_REQUIRES_PAYMENT_METHOD,
        ];
        $templateName = 'aTemplateName';
        $publishableKey = 'aPubKey';

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->will($this->returnCallback(function (GetHttpRequest $request) {
                $request->method = 'POST';
                $request->request = array('stripeToken' => 'theToken');
            }))
        ;

        $action = new RequireConfirmationAction($templateName);
        $action->setGateway($gatewayMock);
        $action->setApi(new Keys($publishableKey, 'secretKey'));

        $action->execute($obtainToken = new RequireConfirmation($model));

        $model = $obtainToken->getModel();
        $this->assertEquals('theToken', $model['payment_intent']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
