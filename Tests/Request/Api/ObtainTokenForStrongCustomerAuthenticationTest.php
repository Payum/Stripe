<?php

namespace Payum\Stripe\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Stripe\Request\Api\ObtainTokenForStrongCustomerAuthentication;
use PHPUnit\Framework\TestCase;

/**
 * Class ObtainTokenForStrongCustomerAuthenticationTest.
 *
 * @author Eric Masoero <em@studeal.fr>
 */
class ObtainTokenForStrongCustomerAuthenticationTest extends TestCase
{
    public function testItShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(ObtainTokenForStrongCustomerAuthentication::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }

    public function testItCouldBeConstructedWithModelAsFirstArgument()
    {
        new ObtainTokenForStrongCustomerAuthentication($model = []);
    }
}
