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

    public function adminAdvertisements(){
        return view('admin.advertisements');
    }

    public function adminAdvertisementCreate(){
        return view('admin.advertisement-create');
    }

    public function adminAdvertisementShow(int $id){
        return view('admin.advertisements-show', ['id' => $id]);
    }   
}
