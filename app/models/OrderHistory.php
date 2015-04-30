<?php namespace models;
/**
 * Email model
 * @author realdark <me@borislazarov.com> on 11 Feb 2015
 */
class OrderHistory extends \core\model {

    function __construct($key = NULL, $select = []) {
        parent::_construct($key, $select);
    }

    /**
     * @param null $order_id
     * @param $productId
     * @param $task
     * @param $task_assignment
     * @internal param $action
     * @return $this|void
     */
    public static function add($order_id, $productId, $task, $task_assignment) {
        //Fetch user id
        $objUser = new User();
        $userId  = $objUser->fetchId();

        $history = new OrderHistory();
        $history->setOrderId($order_id);
        $history->setProductId($productId);
        $history->setUserId($userId);
        $history->setTask($task);
        $history->setTaskAssignment($task_assignment);
        $history->save();
    }

    /**
     * Fetch History
     */
    public static function fetch($order_id) {
        $query = self::for_table(PREFIX . 'order_history')
            ->where('order_id', $order_id)
            ->find_many();

        $str = "";

        foreach ($query as $row) {
            if ($row->task_assignment != 11111111111) {
                $userAction = new User($row->getUserId(), ['name']);
                $userAssignment = new User($row->task_assignment, ['name']);
                $product = new ShoppingCart($row->product_id, ['sku']);

                if ($row->product_id == 0) {
                    $str .= '<p>' . $userAction->name . ' assign ' . $userAssignment->name . ' to task `order was checked by ` on ' . $row->date . '</p>';
                } else {
                    $str .= '<p>' . $product->sku . ' - ' . $userAction->name . ' assign ' . $userAssignment->name . ' to task `' . $row->task . '` on ' . $row->date . '</p>';
                }
            } else {
                $userAction = new User($row->getUserId(), ['name']);
                $product = new ShoppingCart($row->product_id, ['sku']);

                $str .= '<p>Progress of product with sku ' . $product->sku . ' was changed to `' . $row->task . '` by ' . $userAction->name . ' on ' . $row->date . '</p>';
            }
        }

        return $str;
    }

}