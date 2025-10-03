<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class DeliveryPickupController extends Controller
{
    // 納品・集荷選択画面
    public function select(): Response
    {
        return Inertia::render('DeliveryPickup/Select');
    }
}






