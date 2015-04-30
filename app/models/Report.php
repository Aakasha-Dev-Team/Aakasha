<?php namespace models;

use models\User;

/**
 * Report model
 * @author
 */
class Report extends \core\model {
    
    function __construct($key = NULL, $select = []) {
        parent::_construct($key, $select);
    }

    /**
     * Fetch reports
     *
     * $timeframe = [
     *      'mode' => 'pre',
     *      'timeframe' => 'today'
     *  ]
     *
     * $timeframe = [
     *      'mode' => 'custom',
     *      'from_date' => '1.01.2015',
     *      'to_date' => '1.12.2015'
     * ]
     *
     * @param array $timeframe
     * @param $type
     * @param null $customData
     * @param string $chart
     * @return array
     */
    public static function fetchReports($timeframe = [], $type, $customData = null, $chart = 'bar') {
        $vars = self::fetchType($timeframe, $type, $customData, $chart);

        return $vars;
    }

    /**
     * Generate time frame
     *
     * Return  array with clean date.
     *
     * [
     *      'from' => '1-01-1994 00:00:00',
     *      'to' => '1-12-31 23:59:59'
     * ]
     *
     * @param $timeframe
     * @return array
     */
    private static function fetchTimeframe($timeframe) {
        switch ($timeframe) {
            case 'today':
                $data = [
                    'from' => new \DateTime('today 00:00:00'),
                    'to' => new \DateTime('today 23:59:59')
                ];
                break;

            case 'last_day':
                $data = [
                    'from' => new \DateTime('yesterday 00:00:00'),
                    'to' => new \DateTime('yesterday 23:59:59')
                ];
                break;

            case 'current_month':
                $data = [
                    'from' => new \DateTime('first day of this month 00:00:00'),
                    'to' => new \DateTime()
                ];
                break;

            case 'last_month':
                $data = [
                    'from' => new \DateTime('first day of last month 00:00:00'),
                    'to' => new \DateTime('last day of last month 23:59:59')
                ];
                break;

            case 'current_year':
                $data = [
                    'from' => new \DateTime('first day of january this year 00:00:00'),
                    'to' => new \DateTime()
                ];
                break;

            case 'last_year':
                $data = [
                    'from' => new \DateTime('first day of january last year 00:00:00'),
                    'to' => new \DateTime('last day of december last year 23:59:59')
                ];
                break;
        }

        return $data;
    }

    /**
     * fetch type
     *
     * Just processed result to fetchReports();
     */
    private static function fetchType($timeframe, $type, $customData, $chart) {
        if ($timeframe['mode'] === 'pre') {
            $newTimeFrame = self::fetchTimeframe($timeframe['timeframe']);
        } else {
            $from = new \DateTime($timeframe['from_date']);
            $from->setTime(0, 0);

            $to = new \DateTime($timeframe['to_date']);
            $to->setTime(23, 59);

            $newTimeFrame = [
                'from' => $from,
                'to' => $$to
            ];
        }

        if (count($type) > 1) {
            //TODO - foreach all data

            foreach ($type as $value) {
                switch ($value) {
                    case 'orders':
                        $data = self::fetchTypeAllOrders($newTimeFrame, $chart);

                        $chartTitles = $data['titles'];
                        $chartData[] = $data['data'][0];
                        break;

                    case 'order_active':
                        $data = self::fetchTypeActiveOrders($newTimeFrame, $chart);

                        $chartTitles = $data['titles'];
                        $chartData[] = $data['data'][0];
                        break;

                    case 'order_inactive':
                        $data = self::fetchTypeInactiveOrders($newTimeFrame, $chart);

                        $chartTitles = $data['titles'];
                        $chartData[] = $data['data'][0];
                        break;
                }
            }

            $data = [
                'titles' => $chartTitles,
                'data' => $chartData
            ];

        } else {
            switch ($type[0]) {
                case 'orders':
                    $data = self::fetchTypeAllOrders($newTimeFrame, $chart);
                    break;

                case 'order_active':
                    $data = self::fetchTypeActiveOrders($newTimeFrame, $chart);
                    break;

                case 'order_inactive':
                    $data = self::fetchTypeInactiveOrders($newTimeFrame, $chart);
                    break;

                case 'items_all':
                    $sku = $customData;
                    var_dump($sku);
                    break;

                case 'items_custom':
                    $pos = strpos($customData, ',');

                    if ($pos === false) {
                        $sku = $customData;
                        $data = self::fetchTypeItem($newTimeFrame, $sku, $chart);
                    } else {
                        $sku = explode(',', $customData);

                        foreach ($sku as $value) {
                            $newSku = trim($value);
                            $tmpData = self::fetchTypeItem($newTimeFrame, $newSku, $chart);

                            $chartTitles = $tmpData['titles'];
                            $chartData[] = $tmpData['data'][0];
                        }

                        $data = [
                            'titles' => $chartTitles,
                            'data' => $chartData
                        ];
                    }
                    break;

                case 'groups_custom':
                    $pos = strpos($customData, ',');

                    if ($pos === false) {
                        $group = $customData;
                        $data = self::fetchTypeGroup($newTimeFrame, $group, $chart);
                    } else {
                        $group = explode(',', $customData);

                        foreach ($group as $value) {
                            $newGroup = trim($value);
                            $tmpData = self::fetchTypeGroup($newTimeFrame, $newGroup, $chart);

                            $chartTitles = $tmpData['titles'];
                            $chartData[] = $tmpData['data'][0];
                        }

                        $data = [
                            'titles' => $chartTitles,
                            'data' => $chartData
                        ];
                    }
                    break;

                case 'clients_orders':
                    $pos = strpos($customData, ',');

                    if ($pos === false) {
                        $client = $customData;
                        $data = self::fetchTypeClientOrders($newTimeFrame, $client, $chart);
                    } else {
                        $group = explode(',', $customData);

                        foreach ($group as $value) {
                            $newClient = trim($value);
                            $tmpData = self::fetchTypeClientOrders($newTimeFrame, $newClient, $chart);

                            $chartTitles = $tmpData['titles'];
                            $chartData[] = $tmpData['data'][0];
                        }

                        $data = [
                            'titles' => $chartTitles,
                            'data' => $chartData
                        ];
                    }
                    break;

                case 'clients_items':
                    $pos = strpos($customData, ',');

                    if ($pos === false) {
                        $client = $customData;
                        $data = self::fetchTypeClientItems($newTimeFrame, $client, $chart);
                    } else {
                        $group = explode(',', $customData);

                        foreach ($group as $value) {
                            $newClient = trim($value);
                            $tmpData = self::fetchTypeClientItems($newTimeFrame, $newClient, $chart);

                            $chartTitles = $tmpData['titles'];
                            $chartData[] = $tmpData['data'][0];
                        }

                        $data = [
                            'titles' => $chartTitles,
                            'data' => $chartData
                        ];
                    }
                    break;
            }
        }

        return $data;
    }

    /**
     * Most important for Ico
     *
     * Here will be fetch needed data for selected type
     */
    private static function fetchTypeActiveOrders($newTimeFrame, $chart) {
        $query = self::for_table(PREFIX . 'orders')
            ->where('shipped_date', '0000-00-00 00:00:00')
            ->where_raw('order_date BETWEEN ? AND ?', [$newTimeFrame['from']->format('Y-m-d H:i:s'), $newTimeFrame['to']->format('Y-m-d H:i:s')])
            ->find_many();

        $currDate = $newTimeFrame['from']->format('Y-m-d'); //Deprecated. TODO - Delete this var

        $diff = $newTimeFrame['from']->diff($newTimeFrame['to']);
        $interval = $diff->days > 30 ? '1 month' : '1 day';

        //[0] - interval, [1] - measure
        $time = explode(" ", $interval);

        if (count($query) > 0) {
            foreach ($query as $order) {
                $dt = new \DateTime($order->order_date);

                if ($time[1] === 'day') {
                    $date = $dt->format('Y-m-d');
                } else {
                    $date = $dt->format('Y-m');
                }

                if (isset($date)) {
                    $data[$date]++;
                } else {
                    $data[$date] = 1;
                }
            }

        }

        $processedData = self::fillEmptySpaces($data, $newTimeFrame['from'], $newTimeFrame['to'], $interval, 'array');

        foreach ($processedData as $key => $value) {
            $properKey[] = $key;
            $properData[] = $value;
        }

        $vars['titles'] = $properKey;
        $vars['data'][] = [
            'name' => _T('Active orders'),
            'data' => $properData
        ];


        return $vars;
    }

    /**
     * Fetch imactive orders
     *
     * @param $newTimeFrame
     * @param $chart
     */
    private static function fetchTypeInactiveOrders($newTimeFrame, $chart) {
        $query = self::for_table(PREFIX . 'orders')
            ->where_not_equal('shipped_date', '0000-00-00 00:00:00')
            ->where_raw('order_date BETWEEN ? AND ?', [$newTimeFrame['from']->format('Y-m-d H:i:s'), $newTimeFrame['to']->format('Y-m-d H:i:s')])
            ->find_many();

        $currDate = $newTimeFrame['from']->format('Y-m-d'); //Deprecated. TODO - Delete this var

        $diff = $newTimeFrame['from']->diff($newTimeFrame['to']);
        $interval = $diff->days > 30 ? '1 month' : '1 day';

        //[0] - interval, [1] - measure
        $time = explode(" ", $interval);

        if (count($query) > 0) {
            foreach ($query as $order) {
                $dt = new \DateTime($order->order_date);

                if ($time[1] === 'day') {
                    $date = $dt->format('Y-m-d');
                } else {
                    $date = $dt->format('Y-m');
                }

                if (isset($date)) {
                    $data[$date]++;
                } else {
                    $data[$date] = 1;
                }
            }

        }

        $processedData = self::fillEmptySpaces($data, $newTimeFrame['from'], $newTimeFrame['to'], $interval, 'array');

        foreach ($processedData as $key => $value) {
            $properKey[] = $key;
            $properData[] = $value;
        }

        $vars['titles'] = $properKey;
        $vars['data'][] = [
            'name' => _T('Inactive orders'),
            'data' => $properData
        ];


        return $vars;
    }

    /**
     * Fetch all orders for current period
     *
     * @param $newTimeFrame
     * @param $chart
     */
    private static function fetchTypeAllOrders($newTimeFrame, $chart) {
        $query = self::for_table(PREFIX . 'orders')
            ->where_raw('order_date BETWEEN ? AND ?', [$newTimeFrame['from']->format('Y-m-d H:i:s'), $newTimeFrame['to']->format('Y-m-d H:i:s')])
            ->find_many();

        $currDate = $newTimeFrame['from']->format('Y-m-d'); //Deprecated. TODO - Delete this var

        $diff = $newTimeFrame['from']->diff($newTimeFrame['to']);
        $interval = $diff->days > 30 ? '1 month' : '1 day';

        //[0] - interval, [1] - measure
        $time = explode(" ", $interval);

        if (count($query) > 0) {
            foreach ($query as $order) {
                $dt = new \DateTime($order->order_date);

                if ($time[1] === 'day') {
                    $date = $dt->format('Y-m-d');
                } else {
                    $date = $dt->format('Y-m');
                }

                if (isset($date)) {
                    $data[$date]++;
                } else {
                    $data[$date] = 1;
                }
            }

        }

        $processedData = self::fillEmptySpaces($data, $newTimeFrame['from'], $newTimeFrame['to'], $interval, 'array');

        foreach ($processedData as $key => $value) {
            $properKey[] = $key;
            $properData[] = $value;
        }

        $vars['titles'] = $properKey;
        $vars['data'][] = [
            'name' => _T('All orders'),
            'data' => $properData
        ];


        return $vars;
    }

    /**
     * Fetch statistic for current item
     *
     * @param $newTimeFrame
     * @param $sku
     * @param $chart
     * @return
     * @internal param bool $many
     */
    public static function fetchTypeItem($newTimeFrame, $sku, $chart) {
        $query = self::for_table(PREFIX . 'orders')
            ->table_alias('o')
            ->select('o.id', 'order_id')
            ->select('o.order_date')
            ->select('s.quantity')
            ->select('s.sku')
            ->right_outer_join(PREFIX . 'shopping_cart', ['o.id', '=', 's.order_id'], 's')
            ->where_raw('order_date BETWEEN ? AND ?', [$newTimeFrame['from']->format('Y-m-d H:i:s'), $newTimeFrame['to']->format('Y-m-d H:i:s')])
            ->where('s.sku', $sku)
            ->find_many();

        $diff = $newTimeFrame['from']->diff($newTimeFrame['to']);
        $interval = $diff->days > 30 ? '1 month' : '1 day';

        //[0] - interval, [1] - measure
        $time = explode(" ", $interval);

        if (count($query) > 0) {
            foreach ($query as $order) {
                $dt = new \DateTime($order->order_date);

                if ($time[1] === 'day') {
                    $date = $dt->format('Y-m-d');
                } else {
                    $date = $dt->format('Y-m');
                }

                if (isset($date)) {
                    $data[$date] += $order->quantity;
                } else {
                    $data[$date] = $order->quantity;
                }
            }

        }

        $processedData = self::fillEmptySpaces($data, $newTimeFrame['from'], $newTimeFrame['to'], $interval, 'array');

        foreach ($processedData as $key => $value) {
            $properKey[] = $key;
            $properData[] = $value;
        }

        $vars['titles'] = $properKey;
        $vars['data'][] = [
            'name' => $sku,
            'data' => $properData
        ];

        return $vars;
    }

    /**
     * Fetch statistic for current group
     *
     * @param $newTimeFrame
     * @param $group
     * @param $chart
     * @return
     * @internal param $groupName
     */
    public static function fetchTypeGroup($newTimeFrame, $group, $chart) {
        $query = self::for_table(PREFIX . 'orders')
            ->table_alias('o')
            ->select('o.id', 'order_id')
            ->select('o.order_date')
            ->select('s.quantity')
            ->select('s.sku')
            ->select('a.group_description_en')
            ->right_outer_join(PREFIX . 'shopping_cart', ['o.id', '=', 's.order_id'], 's')
            ->join(PREFIX . 'products', ['s.sku', '=', 'a.sku'], 'a')
            ->where_raw('order_date BETWEEN ? AND ?', [$newTimeFrame['from']->format('Y-m-d H:i:s'), $newTimeFrame['to']->format('Y-m-d H:i:s')])
            ->where('a.item_no', $group)
            ->find_many();

        $diff = $newTimeFrame['from']->diff($newTimeFrame['to']);
        $interval = $diff->days > 30 ? '1 month' : '1 day';

        //[0] - interval, [1] - measure
        $time = explode(" ", $interval);

        $groupName = "";

        if (count($query) > 0) {
            foreach ($query as $key => $order) {
                $dt = new \DateTime($order->order_date);

                if ($key === 0) {
                    $groupName = $order->group_description_en;
                }

                if ($time[1] === 'day') {
                    $date = $dt->format('Y-m-d');
                } else {
                    $date = $dt->format('Y-m');
                }

                if (isset($date)) {
                    $data[$date] += $order->quantity;
                } else {
                    $data[$date] = $order->quantity;
                }
            }

        }

        $processedData = self::fillEmptySpaces($data, $newTimeFrame['from'], $newTimeFrame['to'], $interval, 'array');

        foreach ($processedData as $key => $value) {
            $properKey[] = $key;
            $properData[] = $value;
        }

        $vars['titles'] = $properKey;
        $vars['data'][] = [
            'name' => $groupName,
            'data' => $properData
        ];

        return $vars;
    }

    /**
     * Fetch clients orders
     */
    private static function fetchTypeClientOrders($newTimeFrame, $client, $chart) {
        $query = self::for_table(PREFIX . 'orders')
            ->table_alias('o')
            ->select('o.order_date')
            ->select('c.full_name')
            ->join(PREFIX . 'customers', ['o.id', '=', 'c.order_id'], 'c')
            ->where_raw('order_date BETWEEN ? AND ?', [$newTimeFrame['from']->format('Y-m-d H:i:s'), $newTimeFrame['to']->format('Y-m-d H:i:s')])
            ->where('c.full_name', $client)
            ->find_many();

        $diff = $newTimeFrame['from']->diff($newTimeFrame['to']);
        $interval = $diff->days > 30 ? '1 month' : '1 day';

        //[0] - interval, [1] - measure
        $time = explode(" ", $interval);

        $groupName = "";

        if (count($query) > 0) {
            foreach ($query as $key => $order) {
                $dt = new \DateTime($order->order_date);

                if ($key === 0) {
                    $clientName = $order->full_name;
                }

                if ($time[1] === 'day') {
                    $date = $dt->format('Y-m-d');
                } else {
                    $date = $dt->format('Y-m');
                }

                if (isset($date)) {
                    $data[$date]++;
                } else {
                    $data[$date] = 1;
                }
            }

        }

        $processedData = self::fillEmptySpaces($data, $newTimeFrame['from'], $newTimeFrame['to'], $interval, 'array');

        foreach ($processedData as $key => $value) {
            $properKey[] = $key;
            $properData[] = $value;
        }

        $vars['titles'] = $properKey;
        $vars['data'][] = [
            'name' => $clientName,
            'data' => $properData
        ];

        return $vars;
    }

    /**
     * Fetch user purchased items
     *
     * @param $newTimeFrame
     * @param $client
     * @param $chart
     */
    private static function fetchTypeClientItems($newTimeFrame, $client, $chart) {
        $query = self::for_table(PREFIX . 'orders')
            ->table_alias('o')
            ->select('o.id', 'order_id')
            ->select('o.order_date')
            ->select('s.quantity')
            ->select('s.sku')
            ->select('c.full_name')
            ->right_outer_join(PREFIX . 'shopping_cart', ['o.id', '=', 's.order_id'], 's')
            ->join(PREFIX . 'customers', ['o.id', '=', 'c.order_id'], 'c')
            ->where_raw('order_date BETWEEN ? AND ?', [$newTimeFrame['from']->format('Y-m-d H:i:s'), $newTimeFrame['to']->format('Y-m-d H:i:s')])
            ->where('c.full_name', $client)
            ->find_many();

        $diff = $newTimeFrame['from']->diff($newTimeFrame['to']);
        $interval = $diff->days > 30 ? '1 month' : '1 day';

        //[0] - interval, [1] - measure
        $time = explode(" ", $interval);

        $groupName = "";

        if (count($query) > 0) {
            foreach ($query as $key => $order) {
                $dt = new \DateTime($order->order_date);

                if ($key === 0) {
                    $groupName = $order->full_name;
                }

                if ($time[1] === 'day') {
                    $date = $dt->format('Y-m-d');
                } else {
                    $date = $dt->format('Y-m');
                }

                if (isset($date)) {
                    $data[$date] += $order->quantity;
                } else {
                    $data[$date] = $order->quantity;
                }
            }

        }

        $processedData = self::fillEmptySpaces($data, $newTimeFrame['from'], $newTimeFrame['to'], $interval, 'array');

        foreach ($processedData as $key => $value) {
            $properKey[] = $key;
            $properData[] = $value;
        }

        $vars['titles'] = $properKey;
        $vars['data'][] = [
            'name' => $groupName,
            'data' => $properData
        ];

        return $vars;
    }

    /**
     * Just fill with 0 missed data
     */
    private function fillEmptySpaces($vars, \DateTime $start, \DateTime $end, $interval = "1 day", $type) {
        //[0] - interval, [1] - measure
        $time = explode(" ", $interval);

        //Fill empty dates
        $interval = \DateInterval::createFromDateString($interval);
        $period = new \DatePeriod($start, $interval, $end);

        foreach ( $period as $dt ) {
            if ($time[1] === 'month') {
                $date = $dt->format('Y-m');
            } else {
                $date = $dt->format('Y-m-d');
            }

            //Check if $vars is not empty. If i`ts empty create empty array
            if (empty($vars)) {
                $vars = [];
            }

            if(!array_key_exists($date, $vars)) {
                $vars[$date] = 0;
            }
        }

        ksort($vars);

        switch($type) {
            case 'json':
                return json_encode($vars);
            case 'array':
                return $vars;
        }
    }

    /**
     * Fetch curr user reports
     */
    public static function getReports() {
        $objUser = new User();
        $userId  = $objUser->fetchId();

        $query = self::for_table(PREFIX . 'reports')
            ->where('user_id', $userId)
            ->find_array();

        return $query;
    }
}