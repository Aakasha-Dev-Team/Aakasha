<?php namespace models;

/**
 * Report model
 * @author
 */
class OrderCheck extends \core\model {

    function __construct($key = NULL, $select = []) {
        parent::_construct($key, $select);
    }

    /**
     * Order check
     *
     * @param $order_id
     * @param string $type
     * @return bool|\libraries\ORM
     */
    public static function fetchStatus($order_id, $type = 'object') {
        $query = self::for_table(PREFIX . "orders_checked")
            ->where('order_id', $order_id)
            ->find_one();

        if ($type === 'object') {
            return $query;
        } else {
            if (isset($query->order_id)) {
                return 1;
            } else {
                return 0;
            }
        }
    }

}