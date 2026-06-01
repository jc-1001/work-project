<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    public function home()
    {
        return view('frontend.home');
    }

    public function shopIndex()
    {
        return view('frontend.shop-index');
    }

    public function shopShow(int $id)
    {
        $product = \App\Models\Product::where('id', $id)->where('is_active', 1)->firstOrFail();
        return view('frontend.shop-show', ['id' => $id, 'product' => $product]);
    }

    public function adminLogin()
    {
        return view('auth.admin-login');
    }
}
