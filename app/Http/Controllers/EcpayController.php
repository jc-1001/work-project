<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\EcpayService;
use Illuminate\Http\Request;

class EcpayController extends Controller
{
    public function notify(Request $request)
    {
        $data = $request->all();
        $ecpay = new EcpayService();

        if (!$ecpay->verifyCheckMacValue($data)) {
            return response('0|ErrorMessage', 200);
        }

        $order = Order::where('order_number', $data['MerchantTradeNo'])->first();

        if (!$order) {
            return response('0|OrderNotFound', 200);
        }

        if ($data['RtnCode'] === '1') {
            $order->update([
                'payment_status' => 'paid',
                'ecpay_trade_no' => $data['TradeNo'],
            ]);
        } else {
            $order->update(['payment_status' => 'failed']);
        }

        return response('1|OK', 200);
    }

    public function return(Request $request)
    {
        $rtnCode = $request->input('RtnCode');
        $tradeNo = $request->input('MerchantTradeNo');

        if ($rtnCode === '1') {
            return redirect('/shop?payment=success&order=' . $tradeNo);
        }

        return redirect('/order?payment=failed');
    }
}
