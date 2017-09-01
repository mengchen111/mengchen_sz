<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 8/31/17
 * Time: 09:56
 */

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ViewController extends Controller
{
    public function home(Request $request)
    {
        return view('admin.home');
    }

    public function agentList()
    {
        return view('admin.agent.list');
    }

    public function agentCreate()
    {
        return view('admin.agent.create');
    }

    public function topUpAdmin()
    {
        return view('admin.top-up.admin');
    }

    public function topUpAgent()
    {
        return view('admin.top-up.agent');
    }

    public function topUpPlayer()
    {
        return view('admin.top-up.player');
    }

    public function systemLog()
    {
        return view('admin.system.log');
    }
}