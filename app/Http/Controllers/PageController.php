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
        return view('frontend.shop-show', ['id' => $id]);
    }

    public function cart()
    {
        return view('frontend.shop-cart');
    }

    public function order()
    {
        return view('frontend.shop-order');
    }

    public function adminLogin()
    {
        return view('auth.admin-login');
    }
}
