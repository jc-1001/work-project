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

    public function adminLogin()
    {
        return view('auth.admin-login');
    }

    public function adminOrdersIndex()
    {
        return view('admin.orders-index');
    }

    public function adminOrdersShow(int $id)
    {
        return view('admin.orders-show', ['id' => $id]);
    }

}
