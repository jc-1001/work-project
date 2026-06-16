<?php

namespace App\Services;

class EcpayService
{
    private ?string $merchantId;
    private ?string $hashKey;
    private ?string $hashIv;
    private ?string $paymentUrl;

    public function __construct()
    {
        $this->merchantId = config('services.ecpay.merchant_id');
        $this->hashKey    = config('services.ecpay.hash_key');
        $this->hashIv     = config('services.ecpay.hash_iv');
        $this->paymentUrl = config('services.ecpay.payment_url');
    }

    public function buildForm(array $params): string
    {
        $params['CheckMacValue'] = $this->generateCheckMacValue($params);

        $inputs = '';
        foreach ($params as $key => $value) {
            $inputs .= sprintf('<input type="hidden" name="%s" value="%s">', $key, htmlspecialchars($value));
        }

        return sprintf(
            '<form id="ecpay_form" action="%s" method="POST">%s</form>
             <script>document.getElementById("ecpay_form").submit();</script>',
            $this->paymentUrl,
            $inputs
        );
    }

    public function verifyCheckMacValue(array $data): bool
    {
        $received = $data['CheckMacValue'] ?? '';
        unset($data['CheckMacValue']);

        return hash_equals($received, $this->generateCheckMacValue($data));
    }

    private function generateCheckMacValue(array $params): string
    {
        ksort($params);

        $str = 'HashKey=' . $this->hashKey . '&';
        foreach ($params as $k => $v) {
            $str .= "{$k}={$v}&";
        }
        $str .= 'HashIV=' . $this->hashIv;

        $str = strtolower(urlencode($str));
        $str = str_replace(
            ['%2d', '%5f', '%2e', '%21', '%2a', '%28', '%29'],
            ['-',   '_',   '.',   '!',   '*',   '(',   ')'],
            $str
        );

        return strtoupper(hash('sha256', $str));
    }
}
