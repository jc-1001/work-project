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

    public function order()
    {
        return view('frontend.shop-order');
    }

    public function adminLogin()
    {
        return view('auth.admin-login');
    }

    public function couponIndex()
    {
        return view('admin.coupon-index');
    }

    public function couponDetail(int $id)
    {
        return view('admin.coupon-detail', ['id' => $id]);
    }
}
