<?php namespace controllers;
use helpers\request as Request;
use models\Authentication;
use models\Log;
use models\User as User;
use helpers\url as URL;
use helpers\Globals as Globals;

/**
 * Created by PhpStorm.
 * User: realdark
 * Date: 3/20/15
 * Time: 2:02 PM
 */

class LogController extends \core\controller {
    public function __construct() {
        parent::__construct();
    }

    public function index() {
        User::trackLastAction();
        Authentication::chechAuthentication("exit");

        $permission = Globals::get("history_permissions");

        if ($permission['logs']['permission'] == 0) {
            header('Location: /');
        }

        $this->view->addContent([
            'title' => "Log user records",
            'View logs' => _T("View Logs"),
            'History' => _T("History"),
            'View' => _T("View"),
            'from date' => _T("from date"),
            'to date' => _T("to date"),
            'Name' => _T("Name"),
            'Action' => _T("Action"),
            'Date' => _T("Date")
        ]);

        //JavaScript
        $this->view->addContent('js', "
            <script src='" . URL::get_template_path() . "js/jquery.dynatable.js'></script>\n
            <script src='" . URL::get_template_path() . "js/datetime.js'></script>
        ");

        $jq = "
            $(function() {
              $('#from_date_single').datepicker({
                format: 'yyyy-mm-dd'
              });
            });

            $(function() {
              $('#to_date_single').datepicker({
                format: 'yyyy-mm-dd'
              });
            });

            $('#view').click(function(event) {
                event.preventDefault();
                var from = $('#from_date_single').val();
                var to = $('#to_date_single').val();

                //send data
                $.post('/history/fetch_logs_ajax', {
                    from: from,
                    to: to
                }, function(data) {
                    var val = '';

                    $.each( data, function( key, value ) {
                        val += '\
                            <tr>\
                                <td>' + value.name + '</td>\
                                <td>' + value.action + '</td>\
                                <td>' + value.date + '</td>\
                            </tr>\
                        ';
                    });

                    $('table tbody').html(val);
                    $('#log').dynatable();
                }, 'json');
            });
        ";

        $this->view->addContent('jq', $jq);

        $this->view->loadPage("history/log/index");
    }

    /**
     * Fetch logs from to date
     *
     * @internal param $from
     * @internal param $to
     */
    public function fetchLogsAjax() {
        $dateFrom = Request::get('from', 'string');
        $dateTo = Request::get('to', 'string');

        $dt = new \DateTime($dateTo);
        $dt->modify('+1 day');

        $logs = Log::fetchLogs($dateFrom, $dt->format('Y-m-d'));

        echo json_encode($logs);
    }
}