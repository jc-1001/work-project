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

    public function favorite()
    {
        return view('frontend.favorite-index');
    }

    public function adminLogin()
    {
        return view('auth.admin-login');
    }
}
