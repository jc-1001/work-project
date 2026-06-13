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

    public function administratorsIndex()
    {
        return view('admin.administrators-index');
    }

    public function administratorsShow(int $id)
    {
        return view('admin.administrators-show', ['id' => $id]);
    }

    public function adminForbidden()
    {
        return view('admin.forbidden');
    }

    public function forbidden()
    {
        return view('errors.403');
    }
}
