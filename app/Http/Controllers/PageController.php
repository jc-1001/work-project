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

        public function forgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function resetPassword()
    {
        return view('auth.reset-password');
    }

    public function adminLogin()
    {
        return view('auth.admin-login');
    }
}
