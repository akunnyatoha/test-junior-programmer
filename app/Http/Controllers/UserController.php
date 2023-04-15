<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $data = User::latest()->get();
        return view('pages.index', [
            "title" => "Master User",
            "url" => url('/assets'),
            "data" => $data
        ]);
    }


}
