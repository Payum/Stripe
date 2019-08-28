<?php

namespace Payum\Stripe\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Stripe\Request\Api\CreateSetupIntent;
use PHPUnit\Framework\TestCase;

/**
 * Class CreateSetupIntentTest.
 *
 * @author Eric Masoero <em@studeal.fr>
 */
class CreateSetupIntentTest extends TestCase
{
    public function testItShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(CreateSetupIntent::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }

    public function testItCouldBeConstructedWithModelAsFirstArgument()
    {
        new CreateSetupIntent($model = []);
    }
}
