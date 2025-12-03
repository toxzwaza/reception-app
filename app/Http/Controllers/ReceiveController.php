<?php

namespace App\Http\Controllers;

use App\Models\InitialOrder;
use App\Models\Stock;
use Illuminate\Http\Request;

class ReceiveController extends Controller
{
    /**
     * 未納品発注データ取得API
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInitialOrders()
    {
        $initial_orders = InitialOrder::where(function ($query) {
            $query->whereNull('receive_flg') //未納品
                ->orWhere('receive_flg', 0) //未納品
                ->orWhere('receive_flg', 2); //分納
        })->where('del_flg', 0)->get();

        foreach ($initial_orders as $order) {
            $stock = Stock::find($order->stock_id);
            if ($stock) {
                $order->img_path = $stock->img_path;
                $order->stock_id = $stock->id;
            } else {
                $order->not_found_flg = 1;
            
            }
        }

        return response()->json($initial_orders);
    }

    /**
     * 注文先一覧取得API
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getComNames()
    {
        $com_names = InitialOrder::where(function ($query) {
            $query->whereNull('receive_flg')
                ->orWhere('receive_flg', 0);
        })->whereNull('delifile_path')
          ->where('del_flg', 0)
          ->whereNotNull('com_name')
          ->distinct()
          ->pluck('com_name')
          ->filter()
          ->values();

        return response()->json($com_names);
    }
}

