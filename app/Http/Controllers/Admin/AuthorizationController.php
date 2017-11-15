<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AdminRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\AuthorizationMap;

class AuthorizationController extends Controller
{
    use AuthorizationMap;

    public function showViewAccess(AdminRequest $request)
    {
        //TODO
        return $this->view;
    }
}
