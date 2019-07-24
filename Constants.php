<?php
namespace Payum\Stripe;

class Constants
{
    const STATUS_SUCCEEDED = 'succeeded';

    const STATUS_PAID = 'paid';

    const STATUS_FAILED = 'failed';

    const STATUS_REQUIRES_ACTION = 'requires_action';

    const STATUS_REQUIRES_PAYMENT_METHOD = 'requires_payment_method';

    private function __construct()
    {
    }
}
