<?php

namespace Payum\Stripe\Tests\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Tests\GenericActionTest;
use Payum\Stripe\Action\ConfirmAction;
use Payum\Stripe\Request\Api\ConfirmPaymentIntent;
use Payum\Stripe\Request\Confirm;
use Payum\Stripe\Request\RequireConfirmation;

/**
 * Class ConfirmActionTest.
 *
 * @author Eric Masoero <em@studeal.fr>
 */
class ConfirmActionTest extends GenericActionTest
{
    protected $requestClass = Confirm::class;

    protected $actionClass = ConfirmAction::class;

    public function testItShouldImplementsGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(ConfirmAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testItShouldSubExecutesRequireConfirmationRequestIfTokenNotSet()
    {
        $model = [];

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(RequireConfirmation::class))
        ;

        $action = new ConfirmAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Confirm($model));
    }

    public function testItShouldSubExecuteRequireConfirmationRequestWithCurrentModel()
    {
        $model = new \ArrayObject(['foo' => 'fooVal']);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->will($this->returnCallback(function (RequireConfirmation $request) use ($model) {
                $this->assertInstanceOf(ArrayObject::class, $request->getModel());
                $this->assertSame(['foo' => 'fooVal'], (array) $request->getModel());
            }))
        ;

        $action = new ConfirmAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Confirm($model));
    }

    public function testItShouldSubExecuteConfirmPaymentIntentIfTokenSetButNotUsed()
    {
        $model = [
            'payment_intent' => 'notUsedToken',
        ];

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(ConfirmPaymentIntent::class))
        ;

        $action = new ConfirmAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Confirm($model));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
