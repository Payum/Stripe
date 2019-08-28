<?php

namespace Payum\Stripe\Tests\Request;

use Payum\Core\Request\Generic;
use Payum\Stripe\Request\RequireConfirmation;
use PHPUnit\Framework\TestCase;

/**
 * Class RequireConfirmationTest.
 *
 * @author Eric Masoero <em@studeal.fr>
 */
class RequireConfirmationTest extends TestCase
{
    public function testItShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(RequireConfirmation::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }

    public function testItCouldBeConstructedWithModelAsFirstArgument()
    {
        new RequireConfirmation($model = []);
    }
}
