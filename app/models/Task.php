<?php namespace models;
/**
 * Email model
 * @author realdark <me@borislazarov.com> on 11 Feb 2015
 */
class Task extends \core\model {

    function __construct($key = NULL, $select = []) {
        parent::_construct($key, $select);
    }

    /**
     * @param $product_id
     * @param $action
     * @return array
     */
    public static function fetchTask($product_id, $action) {
        $query = self::for_table(PREFIX . "tasks")
            ->where('product_id', $product_id)
            ->where('task', $action)
            ->find_one();

        return $query;
    }

    /**
     * Create new task
     */
    public static function createTask($user_id, $product_id, $action) {
        $task = new Task(['product_id' => $product_id, 'task' => $action]);
        $taskId = $task->getId();

        if (isset($taskId)) {
            $task->setTask($action);
            $task->setUserId($user_id);
            $task->save();
        } else {
            $task = new Task();
            $task->setProductId($product_id);
            $task->setTask($action);
            $task->setUserId($user_id);
            $task->save();
        }
    }

}