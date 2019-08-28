<?php

namespace Payum\Stripe\Tests\Request;

use Payum\Core\Request\Generic;
use Payum\Stripe\Request\Confirm;
use PHPUnit\Framework\TestCase;

/**
 * Class ConfirmTest.
 *
 * @author Eric Masoero <em@studeal.fr>
 */
class ConfirmTest extends TestCase
{
    public function testItShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(Confirm::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }

    public function testItCouldBeConstructedWithModelAsFirstArgument()
    {
        new Confirm($model = []);
    }
}
