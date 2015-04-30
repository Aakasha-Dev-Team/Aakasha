<?php namespace models;
/**
 * Created by PhpStorm.
 * User: realdark
 * Date: 3/17/15
 * Time: 10:04 AM
 */

class Log extends \core\model {

    function __construct($key = NULL, $select = []) {
        parent::_construct($key, $select);
    }

    /**
     * Log User Activate
     */
    public static function logMe($action = null) {
        //Fetch user id
        $objUser = new User();
        $userId  = $objUser->fetchId();

        $objLog = new Log();
        $objLog->setUserId($userId);
        $objLog->setAction($action);
        $objLog->setUrl("http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        $objLog->save();
    }

    /**
     * Fetch logs
     *
     * @param $from
     * @param $to
     * @return array|\IdiormResultSet
     */
    public static function fetchLogs($from, $to) {
        $query = self::for_table(PREFIX . "log")
            ->table_alias('l')
            ->select('l.*')
            ->select('u.name')
            ->join(PREFIX . 'users', array('l.user_id', '=', 'u.id'), 'u')
            ->where_raw('(`date` BETWEEN ? AND ?)', array($from, $to))
            ->find_array();

        return $query;
    }

    /**
     * Fetch last user logs
     */
    public static function fetchLastUserLogs($id, $limit = 10) {
        $query = self::for_table(PREFIX . "log")
            ->where('user_id', $id)
            ->limit($limit)
            ->order_by_desc('date')
            ->find_array();

        return $query;
    }

    /**
     * Fetch order log
     */
    public static function fetchLogForOrder($product_id) {
        $query = self::for_table(PREFIX . 'log')
            ->where_like('action', '%with id ' . $product_id . '%')
            ->find_many();

        $str = "";

        foreach ($query as $row) {
            $userAction = new User($row->user_id, ['name']);

            $str .= "<p>" . $userAction->name . " - " . $row->action . "</p>";
        }

        return $str;
    }
}