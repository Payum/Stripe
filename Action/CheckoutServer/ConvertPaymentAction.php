<?php

namespace Payum\Stripe\Action\CheckoutServer;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;

class ConvertPaymentAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param Convert $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        $details = ArrayObject::ensureArrayObject($payment->getDetails());
        $details['customer_email'] = $payment->getClientEmail();
        $details['client_reference_id'] = $payment->getNumber();
        $details['payment_intent_data'] = [
            'description' => $payment->getDescription(),
            'metadata' => [
                'paymentNumber' => $payment->getNumber(),
            ],
        ];
        $details['line_items'] = [[
            'name' => $payment->getDescription(),
            'amount' => $payment->getTotalAmount(),
            'currency' => $payment->getCurrencyCode(),
            'quantity' => 1
        ]];
        $details['payment_method_types'] = ['card'];
        $details['mode'] = 'payment';
        $details['submit_type'] = 'pay';
        $details['locale'] = 'auto';

        $request->setResult((array) $details);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            $request->getTo() == 'array'
        ;
    }
}
