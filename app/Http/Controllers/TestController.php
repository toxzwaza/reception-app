<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;


class TestController extends Controller
{
    //
    public function test(){
        $groups = Group::where('phone_number', "!=" , null )->get();
        dd($groups);
    }
}
