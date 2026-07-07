<?php

namespace App\Http\Controllers;

use App\Models\InitialOrder;
use App\Models\Stock;
use App\Models\StockStorage;
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
                $order->quantity_per_org = $stock->quantity_per_org; // 換算値（発注単位あたりの個数）
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

    /**
     * 物品(stock)の格納先候補取得API
     *
     * 在庫加算先の選択肢として、指定 stock に登録された格納先一覧を返す。
     * 格納先が未登録の場合は空配列（フロント側で在庫加算をスキップ）。
     *
     * @param  int  $stock  stock_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStockStorages($stock)
    {
        $storages = StockStorage::with('storageAddress')
            ->where('stock_id', $stock)
            ->get()
            ->map(function ($ss) {
                return [
                    'storage_address_id' => $ss->storage_address_id,
                    'current_quantity'   => $ss->quantity,
                    'label'              => $ss->storageAddress ? $ss->storageAddress->label : ('#' . $ss->storage_address_id),
                ];
            })
            ->values();

        return response()->json($storages);
    }
}

