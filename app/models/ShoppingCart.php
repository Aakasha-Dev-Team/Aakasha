<?php namespace models;
use helpers\Util;

/**
 * ShoppingCart model
 * @author
 */
class ShoppingCart extends \core\model {
    
    function __construct($key = NULL, $select = []) {
        parent::_construct($key, $select);
    }
    
    /**
     * Fetch all shipping rates
     * @author Bobi <me@borislazarov.com> on 5 Dec 2014
     * @return object
     */
    public function fetchRates() {
        $result = self::for_table(PREFIX . "shipping_rates")->find_many();
        return $result;
    }

    /**
     * Purchased items
     */
    public static function purchasedItems() {
        $query = self::for_table(PREFIX . 'orders')
            ->table_alias('o')
            ->select('s.name_en')
            ->select('s.quantity')
            ->select('s.size')
            ->select('s.color')
            ->select('s.sku')
            ->right_outer_join(PREFIX . 'shopping_cart', array('o.id', '=', 's.order_id'), 's')
            ->where('o.shipped_date', '0000-00-00 00:00:00')
            ->where_not_null('sku')
            ->order_by_asc('sku')
            ->find_many();

        $arrCountItems = [];

        foreach ($query as $item) {
            if (isset($arrCountItems[$item->sku])) {
                $arrCountItems[$item->sku . '-' . $item->size . '-' . $item->color]['quantity'] += $item->quantity;
            } else {
                $arrCountItems[$item->sku . '-' . $item->size . '-' . $item->color] = [
                    'sku' => $item->sku,
                    'name_en' => $item->name_en,
                    'quantity' => $item->quantity,
                    'size' => $item->size ? $item->size : 'unknown',
                    'color' => $item->color ? $item->color : 'unknown'
                ];
            }
        }

        return $arrCountItems;
    }
}