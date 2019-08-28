<?php

namespace Payum\Stripe\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Stripe\Request\Api\ConfirmPaymentIntent;
use PHPUnit\Framework\TestCase;

/**
 * Class ConfirmPaymentIntentTest.
 *
 * @author Eric Masoero <em@studeal.fr>
 */
class ConfirmPaymentIntentTest extends TestCase
{
    public function testItShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(ConfirmPaymentIntent::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }

    public function testItCouldBeConstructedWithModelAsFirstArgument()
    {
        new ConfirmPaymentIntent($model = []);
    }
}
