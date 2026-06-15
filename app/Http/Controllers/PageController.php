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

    public function adminProductsIndex()
    {
        return view('admin.products-index');
    }

    public function adminProductsCreate()
    {
        return view('admin.products-store');
    }

    public function adminProductsShow(int $id)
    {
        return view('admin.products-show', ['id' => $id]);
    }

    public function adminUsersIndex()
    {
        return view('admin.user-index');
    }

    public function adminUsersShow(int $id)
    {
        return view('admin.user-show', ['id' => $id]);
    }

    public function adminOrdersIndex()
    {
        return view('admin.orders-index');
    }

    public function adminOrdersShow(int $id)
    {
        return view('admin.orders-show', ['id' => $id]);
    }

    public function adminAdvertisements()
    {
        return view('admin.advertisements');
    }

    public function adminAdvertisementCreate()
    {
        return view('admin.advertisement-create');
    }

    public function adminAdvertisementShow(int $id)
    {
        return view('admin.advertisements-show', ['id' => $id]);
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

    public function couponIndex()
    {
        return view('admin.coupon-index');
    }

    public function couponDetail(int $id)
    {
        return view('admin.coupon-detail', ['id' => $id]);
    }

    public function adminDashboard()
    {
        return view('admin.dashboard');
    }

    public function adminReplyList()
    {
        return view('admin.customer-service-index');
    }

    public function adminReply($id)
    {
        return view('admin.customer-service-reply', ['id' => $id]);
    }

    public function adminComplaintIndex()
    {
        return view('admin.complaints-index');
    }

    public function adminComplaintShow(int $id)
    {
        return view('admin.complaints-show', ['id' => $id]);
    }
}
