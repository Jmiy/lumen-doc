<?php
namespace App\Util\Payment\Paypal;

use PayPal\Api\Payment;


class PayPalRest extends Payment
{
    /**
     * Creates and processes a payment. In the JSON request body, include a `payment` object with the intent, payer, and transactions. For PayPal payments, include redirect URLs in the `payment` object.
     *
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return Payment
     */
    public function createPay($apiContext = null, $restCall = null,$header=[])
    {
        $payLoad = $this->toJSON();
        $json = self::executeCall(
            "/v1/payments/payment",
            "POST",
            $payLoad,
            $header,
            $apiContext,
            $restCall
        );
        $this->fromJson($json);
        return $this;
    }
}

?>