<?php

namespace App\Models;

use App\Models\Order;
use App\Models\Shipping;

/**
 * Order Helper Class
 * 
 * Provides helper methods for order calculations and operations
 */
class OrderHelper
{
    /**
     * Get grand price with shipping and coupon
     *
     * @param int $id
     * @param int $user_id
     * @return float
     */
    public static function getGrandPrice($id, $user_id)
    {
        $order = Order::find($id);
        if ($order) {
            $shipping_price = (float) ($order->shipping->price ?? 0);
            $order_price = self::getOrderPrice($id, $user_id);
            return number_format((float) ($order_price + $shipping_price), 2, '.', '');
        }
        return 0;
    }

    /**
     * Get order price
     *
     * @param int $id
     * @param int $user_id
     * @return float
     */
    public static function getOrderPrice($id, $user_id)
    {
        $order = Order::find($id);
        if ($order) {
            return $order->cart_info->sum('price');
        }
        return 0;
    }

    /**
     * Get earning per month
     *
     * @return float
     */
    public static function getEarningPerMonth()
    {
        $month_data = Order::where('status', 'delivered')->get();
        $price = 0;
        foreach ($month_data as $data) {
            $price += $data->cart_info->sum('price');
        }
        return number_format((float) $price, 2, '.', '');
    }

    /**
     * Get all shipping options
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllShipping()
    {
        return Shipping::orderBy('id', 'DESC')->get();
    }
}
