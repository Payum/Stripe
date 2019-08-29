<?php

namespace Payum\Stripe\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Stripe\Request\Api\CreatePaymentIntent;
use PHPUnit\Framework\TestCase;

/**
 * Class CreatePaymentIntentTest.
 *
 * @author Eric Masoero <em@studeal.fr>
 */
class CreatePaymentIntentTest extends TestCase
{
    public function testItShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(CreatePaymentIntent::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }

    public function testItCouldBeConstructedWithModelAsFirstArgument()
    {
        new CreatePaymentIntent($model = []);
    }
}
