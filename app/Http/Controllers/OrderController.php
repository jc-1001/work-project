<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', auth()->id())
                       ->with('items.product')
                       ->orderBy('created_at', 'desc')
                       ->paginate(2);

        return response()->json($orders);
    }

    
}
