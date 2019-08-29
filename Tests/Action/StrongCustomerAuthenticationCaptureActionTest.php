<?php

namespace Payum\Stripe\Tests\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Tests\GenericActionTest;
use Payum\Stripe\Action\StrongCustomerAuthenticationCaptureAction;
use Payum\Stripe\Constants;
use Payum\Stripe\Request\Api\CreatePaymentIntent;
use Payum\Stripe\Request\Api\ObtainTokenForStrongCustomerAuthentication;

/**
 * Class StrongCustomerAuthenticationCaptureActionTest.
 *
 * @author Eric Masoero <em@studeal.fr>
 */
class StrongCustomerAuthenticationCaptureActionTest extends GenericActionTest
{
    protected $requestClass = Capture::class;

    protected $actionClass = StrongCustomerAuthenticationCaptureAction::class;

    public function testItShouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(StrongCustomerAuthenticationCaptureAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testItShouldDoNothingIfPaymentHasStatus()
    {
        $model = [
            'status' => Constants::STATUS_SUCCEEDED,
        ];

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new StrongCustomerAuthenticationCaptureAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Capture($model));
    }

    public function testItShouldSubExecuteObtainTokenForStrongCustomerAuthenticationRequestIfTokenNotSet()
    {
        $model = [];

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(ObtainTokenForStrongCustomerAuthentication::class))
        ;

        $action = new StrongCustomerAuthenticationCaptureAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Capture($model));
    }

    public function testItShouldSubExecuteObtainTokenForStrongCustomerAuthenticationRequestWithCurrentModel()
    {
        $model = new \ArrayObject(['foo' => 'fooVal']);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->will($this->returnCallback(function (ObtainTokenForStrongCustomerAuthentication $request) use ($model) {
                $this->assertInstanceOf(ArrayObject::class, $request->getModel());
                $this->assertSame(['foo' => 'fooVal'], (array) $request->getModel());
            }))
        ;

        $action = new StrongCustomerAuthenticationCaptureAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Capture($model));
    }

    public function testItShouldSubExecuteCreatePaymentIntentIfTokenSetButNotUsed()
    {
        $model = [
            'payment_method' => 'notUsedToken',
        ];

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(CreatePaymentIntent::class))
        ;

        $action = new StrongCustomerAuthenticationCaptureAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Capture($model));
    }

    public function testItShouldSubExecuteCreatePaymentIntentIfCustomerSet()
    {
        $model = [
            'customer' => 'theCustomerId',
        ];

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(CreatePaymentIntent::class))
        ;

        $action = new StrongCustomerAuthenticationCaptureAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Capture($model));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
