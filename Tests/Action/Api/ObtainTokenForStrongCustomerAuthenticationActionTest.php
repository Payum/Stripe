<?php

namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;
use Payum\Stripe\Action\Api\ObtainTokenForStrongCustomerAuthenticationAction;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\ObtainTokenForStrongCustomerAuthentication;
use PHPUnit\Framework\TestCase;

/**
 * Class ObtainTokenForStrongCustomerAuthenticationActionTest.
 *
 * @author Eric Masoero <em@studeal.fr>
 */
class ObtainTokenForStrongCustomerAuthenticationActionTest extends TestCase
{
    public function testItShouldImplementsGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(ObtainTokenForStrongCustomerAuthenticationAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testItShouldImplementsApiAwareInterface()
    {
        $rc = new \ReflectionClass(ObtainTokenForStrongCustomerAuthenticationAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    public function testItCouldBeConstructedWithTemplateAsFirstArgument()
    {
        new ObtainTokenForStrongCustomerAuthenticationAction('aTemplateName');
    }

    public function testItShouldAllowsSetKeysAsApi()
    {
        $action = new ObtainTokenForStrongCustomerAuthenticationAction('aTemplateName');

        $action->setApi(new Keys('publishableKey', 'secretKey'));
    }

    /**
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     */
    public function testItThrowsNotSupportedApiIfNotKeysGivenAsApi()
    {
        $action = new ObtainTokenForStrongCustomerAuthenticationAction('aTemplateName');

        $action->setApi('not keys instance');
    }

    public function testItShouldSupportsObtainTokenForStrongCustomerAuthenticationRequestWithArrayAccessModel()
    {
        $action = new ObtainTokenForStrongCustomerAuthenticationAction('aTemplateName');

        $this->assertTrue($action->supports(new ObtainTokenForStrongCustomerAuthentication([])));
    }

    public function testItShouldNotSupportObtainTokenForStrongCustomerAuthenticationRequestWithNotArrayAccessModel()
    {
        $action = new ObtainTokenForStrongCustomerAuthenticationAction('aTemplateName');

        $this->assertFalse($action->supports(new ObtainTokenForStrongCustomerAuthentication(new \stdClass())));
    }

    public function testItShouldNotSupportNotObtainTokenForStrongCustomerAuthenticationRequest()
    {
        $action = new ObtainTokenForStrongCustomerAuthenticationAction('aTemplateName');

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     * @expectedExceptionMessage Action ObtainTokenForStrongCustomerAuthenticationAction is not supported the request stdClass.
     */
    public function testItThrowsRequestNotSupportedIfNotSupportedGiven()
    {
        $action = new ObtainTokenForStrongCustomerAuthenticationAction('aTemplateName');

        $action->execute(new \stdClass());
    }

    /**
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The token has already been set.
     */
    public function testItThrowsIfModelAlreadyHaveTokenSet()
    {
        $action = new ObtainTokenForStrongCustomerAuthenticationAction('aTemplateName');

        $action->execute(new ObtainTokenForStrongCustomerAuthentication(array(
            'payment_method' => 'aToken',
        )));
    }

    public function testItShouldRenderExpectedTemplateIfHttpRequestNotPOST()
    {
        $model = new \ArrayObject();
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

        $action = new ObtainTokenForStrongCustomerAuthenticationAction($templateName);
        $action->setGateway($gatewayMock);
        $action->setApi(new Keys($publishableKey, 'secretKey'));

        try {
            $action->execute(new ObtainTokenForStrongCustomerAuthentication($model));
        } catch (HttpResponse $reply) {
            $this->assertEquals('theContent', $reply->getContent());

            return;
        }

        $this->fail('HttpResponse reply was expected to be thrown.');
    }

    public function testItShouldRenderTemplateIfHttpRequestPOSTButNotContainStripeToken()
    {
        $model = [];
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

        $action = new ObtainTokenForStrongCustomerAuthenticationAction($templateName);
        $action->setGateway($gatewayMock);
        $action->setApi(new Keys($publishableKey, 'secretKey'));

        try {
            $action->execute(new ObtainTokenForStrongCustomerAuthentication($model));
        } catch (HttpResponse $reply) {
            return;
        }

        $this->fail('HttpResponse reply was expected to be thrown.');
    }

    public function testItShouldSetTokenFromHttpRequestToObtainTokenRequestOnPOST()
    {
        $model = [];
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

        $action = new ObtainTokenForStrongCustomerAuthenticationAction($templateName);
        $action->setGateway($gatewayMock);
        $action->setApi(new Keys($publishableKey, 'secretKey'));

        $action->execute($obtainToken = new ObtainTokenForStrongCustomerAuthentication($model));

        $model = $obtainToken->getModel();
        $this->assertEquals('theToken', $model['payment_method']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
