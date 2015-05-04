<?php

namespace controllers;

use helpers\request as Request;
use helpers\Util;
use models\Authentication;
use models\Report;
use models\User;
use helpers\session as Session;

/**
 * Created by PhpStorm.
 * User: realdark
 * Date: 4/15/15
 * Time: 9:31 AM
 */
class ReportController extends \core\controller {

    public function __construct() {
        parent::__construct();
    }

    /**
     * New reports
     */
    public function newReport() {
        Authentication::chechAuthentication("exit");

        //Track last action
        User::trackLastAction();

        $success = Session::pull('success');
        $error = Session::pull('error');

        //Add information in template
        $this->view->addContent([
            "title" => _T("Reports - New"),
            "Home" => _T("Home"),
            "Reports" => _T("Reports"),
            "New" => _T("New"),
            "Name" => _T("Name"),
            "Type name" => _T("Type name"),
            "today" => _T("Today"),
            "last_day" => _T("Last day"),
            "current_month" => _T("Current month"),
            "last_month" => _T("Last month"),
            "current_year" => _T("Current year"),
            "last_year" => _T("Last year"),
            "custom" => _T("Custom"),
            "orders" => _T("Orders"),
            "item" => _T("Item"),
            "groups" => _T("Groups"),
            "generate" => _T("Generate"),
            "save" => _T("Save"),
            "timeframe" => _T("Time frame"),
            "type" => _T("Type"),
            "chart" => _T("Chart"),
            "custom_date" => _T("Custom date"),
            "from_date" => _T("From date"),
            "to_date" => _T("To date"),
            "order_active" => _T("Active"),
            "order_inactive" => _T("Inactive"),
            "unit" => _T("Note"),
            "unit-note" => _T("The Unit will be automatically determined (day or month)!"),
            "order_all" => _T("All"),
            "other" => _T("Other"),
            "clients" => _T("Clients"),
            "groups_all" => _T("All"),
            'groups_custom' => _T('Custom'),
            'items' => _T('Items'),
            'custom_data' => _T('Custom'),
            'items_all' => _T('All'),
            'items_custom' => _T('Custom'),
            'user_orders' => _T('Orders'),
            'user_items' => _T('Items'),
            'success' => $success,
            'error' => $error
        ]);

        $jq = "
            function chart(chartTitles, chartData) {
                $(function () {
                    $('#chartdiv').highcharts({
                        chart: {
                            type: 'column'
                        },
                        title: {
                            text: 'Chart'
                        },
                        subtitle: {
                            text: 'Source: appdev.aakasha.com'
                        },
                        xAxis: {
                            categories: chartTitles,
                            crosshair: true
                        },
                        yAxis: {
                            min: 0,
                            title: {
                                text: 'Stats'
                            }
                        },
                        plotOptions: {
                            column: {
                                pointPadding: 0.2,
                                borderWidth: 0
                            },
                            series: {
                                dataLabels: {
                                    enabled: true,
                                    format: '{point.y:.1f}'
                                }
                            }
                        },
                        series: chartData
                    });
                });
            }

            $('#from_date').datepicker({
                format: 'yyyy-mm-dd'
            });

            $('#to_date').datepicker({
                format: 'yyyy-mm-dd'
            });

            //Show custom date
            $('#timeframe').change(function() {
                var frame = $(this).find(':selected').val();

                if (frame === 'custom') {
                    $('#custom_frame').removeClass('hidden');
                } else {
                    $('#custom_frame').addClass('hidden');
                }
            });

            //Show item field
            $('#type').change(function() {
                var type = $(this).find(':selected').val();

                if ((type === 'items_custom') || (type === 'groups_custom') || (type === 'clients_orders') || (type === 'clients_items')) {
                    $('#item_field').removeClass('hidden');
                } else {
                    $('#item_field').addClass('hidden');
                }
            });

            //Generate chart
            $('#generate').click(function() {
                $.post('/reports/generate', $('#report-details').serialize(), function(data) {
                    var titles = data.titles;
                    var chartData = data.data;

                    //generate chart
                    chart(titles, chartData);
                }, 'json');
            });

            $('#type, #timeframe, #chart').chosen();
        ";

        $this->view->addContent("jq", $jq);

        //Render template
        $this->view->loadPage("reports/new");
    }

    /**
     * Generate report thoth ajax
     */
    public function generateReport() {
        $from_date = Request::get('from_date', 'string');
        $to_date = Request::get('to_date', 'string');
        $timeframe = Request::get('timeframe', 'string');
        $type = $_POST['type'];
        $item = Request::get('item', 'string');
        $chart = Request::get('char', 'string');

        $customData = empty($item) ? null : $item;

        if ($timeframe === 'custom') {
            $report = Report::fetchReports(['mode' => 'custom', 'from_date' => $from_date, 'to_date' => $to_date], $type, $customData, $chart);
        } else {
            $report = Report::fetchReports(['mode' => 'pre', 'timeframe' => $timeframe], $type, $customData, $chart);
        }

        echo json_encode($report);
    }

    /**
     * Save reports
     */
    public function saveReport() {
        //Track last action
        User::trackLastAction();

        $is_valid = \libraries\gump::is_valid($_POST, array(
                    'name' => 'required',
                    'timeframe' => 'required',
                    'type' => 'required',
                    'chart' => 'required'
        ));

        $error = "";

        if ($is_valid === true) {
            $objUser = new User();
            $userId = $objUser->fetchId();

            $name = $_POST['name'];
            $timeframe = $_POST['timeframe'];
            $from_date = $_POST['from_date'];
            $to_date = $_POST['to_date'];
            $item = $_POST['item'];
            $type = $_POST['type'];
            $chart = $_POST['chart'];

            $objReport = new Report();
            $objReport->setUserId($userId);
            $objReport->setName($name);

            if ($timeframe === 'custom') {
                $objReport->setTimeframe($timeframe);

                $ctf = json_encode([
                    'from' => $from_date,
                    'to' => $to_date
                ]);

                $objReport->setCustomTimeframe($ctf);
            } else {
                $objReport->setTimeframe($timeframe);
            }

            $objReport->setType(json_encode($type));

            if (!empty($item)) {
                $objReport->setCustom($item);
            }

            $objReport->setChart($chart);

            $objReport->save();

            Session::set('success', _T('Your data has been successfully saved.'));
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        } else {
            foreach ($is_valid as $e) {
                $error .= $e . '<br>';
            }

            Session::set('error', $error);
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        }
    }

    /**
     * Display saved reports
     */
    public function viewReports() {
        Authentication::chechAuthentication("exit");

        //Track last action
        User::trackLastAction();

        $success = Session::pull('success');
        $error = Session::pull('error');

        //Add information in template
        $this->view->addContent([
            "title" => _T("Reports - Show"),
            "Home" => _T("Home"),
            "Reports" => _T("Reports"),
            "show" => _T("Show"),
            'success' => $success,
            'error' => $error,
            'Name' => _T("Name"),
            'Created at' => _T("Created at"),
            'Actions' => _T("Actions"),
            'Choose' => _T("Choose"),
            'View' => _T("View"),
            'Edit' => _T("Edit"),
            'Delete' => _T("Delete")
        ]);

        $this->view->addContent("reports", Report::getReports());

        $jq = "";

        $this->view->addContent("jq", $jq);

        //Render template
        $this->view->loadPage("reports/show");
    }

    /**
     * Delete report
     */
    public function deleteReport($id) {
        $objReport = new Report($id);
        $reportId = $objReport->getId();

        if (isset($reportId)) {
            $objReport->delete();

            Session::set('success', _T('Your data has been successfully deleted.'));
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        } else {
            Session::set('error', _T("There is such report!"));
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        }
    }

    public function showReport($id) {
        Authentication::chechAuthentication("exit");

        //Track last action
        User::trackLastAction();

        $success = Session::pull('success');
        $error = Session::pull('error');

        $objReport = new Report($id);
        $reportId = $objReport->getId();

        $objUser = new User();
        $userId = $objUser->fetchId();

        //Check if report exist
        if (!isset($reportId)) {
            Session::set('error', _T("There is such report!"));
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        }

        //Check if report is for logged user
        if ($userId != $objReport->getUserId()) {
            Session::set('error', _T("This is not your report!"));
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        }

        //fetch report details
        $customDates = json_decode($objReport->getCustomTimeframe());
        $types = json_decode($objReport->getType());
        $reportName = $objReport->getName();

        //report details
        $from_date = $customDates['from'];
        $to_date = $customDates['to'];
        $timeframe = $objReport->getTimeframe();
        $type = $types;
        $item = $objReport->getCustom();
        $chart = $objReport->getChart();

        $customData = empty($item) ? null : $item;

        if ($timeframe === 'custom') {
            $report = Report::fetchReports(['mode' => 'custom', 'from_date' => $from_date, 'to_date' => $to_date], $type, $customData, $chart);
        } else {
            $report = Report::fetchReports(['mode' => 'pre', 'timeframe' => $timeframe], $type, $customData, $chart);
        }

        //Add information in template
        $this->view->addContent([
            "title" => _T("Reports - Show"),
            "Home" => _T("Home"),
            "Reports" => _T("Reports"),
            "details" => _T("Details"),
            'success' => $success,
            'error' => $error,
            'report' => json_encode($report),
            'report_name' => $reportName
        ]);

        $jq = "
            function chart(chartTitles, chartData) {
                $(function () {
                    $('#chartdiv').highcharts({
                        chart: {
                            type: 'column'
                        },
                        title: {
                            text: '{report_name}'
                        },
                        subtitle: {
                            text: 'Source: appdev.aakasha.com'
                        },
                        xAxis: {
                            categories: chartTitles,
                            crosshair: true
                        },
                        yAxis: {
                            min: 0,
                            title: {
                                text: 'Stats'
                            }
                        },
                        plotOptions: {
                            column: {
                                pointPadding: 0.2,
                                borderWidth: 0
                            },
                            series: {
                                dataLabels: {
                                    enabled: true,
                                    format: '{point.y:.1f}'
                                }
                            }
                        },
                        series: chartData
                    });
                });
            }
            
            var chartData = {report};
            
            var titles = chartData.titles;
            var chartData = chartData.data;

            //generate chart
            chart(titles, chartData);
        ";

        $this->view->addContent("jq", $jq);

        //Render template
        $this->view->loadPage("reports/details");
    }
    
    /**
     * Edit report html
     */
    public function editReport($id) {
        Authentication::chechAuthentication("exit");

        //Track last action
        User::trackLastAction();

        $success = Session::pull('success');
        $error = Session::pull('error');

        //Add information in template
        $this->view->addContent([
            "title" => _T("Reports - Edit"),
            "Home" => _T("Home"),
            "Reports" => _T("Reports"),
            "New" => _T("New"),
            "Name" => _T("Name"),
            "Type name" => _T("Type name"),
            "today" => _T("Today"),
            "last_day" => _T("Last day"),
            "current_month" => _T("Current month"),
            "last_month" => _T("Last month"),
            "current_year" => _T("Current year"),
            "last_year" => _T("Last year"),
            "custom" => _T("Custom"),
            "orders" => _T("Orders"),
            "item" => _T("Item"),
            "groups" => _T("Groups"),
            "generate" => _T("Generate"),
            "save" => _T("Save"),
            "timeframe" => _T("Time frame"),
            "type" => _T("Type"),
            "chart" => _T("Chart"),
            "custom_date" => _T("Custom date"),
            "from_date" => _T("From date"),
            "to_date" => _T("To date"),
            "order_active" => _T("Active"),
            "order_inactive" => _T("Inactive"),
            "unit" => _T("Note"),
            "unit-note" => _T("The Unit will be automatically determined (day or month)!"),
            "order_all" => _T("All"),
            "other" => _T("Other"),
            "clients" => _T("Clients"),
            "groups_all" => _T("All"),
            'groups_custom' => _T('Custom'),
            'items' => _T('Items'),
            'custom_data' => _T('Custom'),
            'items_all' => _T('All'),
            'items_custom' => _T('Custom'),
            'user_orders' => _T('Orders'),
            'user_items' => _T('Items'),
            'success' => $success,
            'error' => $error
        ]);
        
        $objReport = new Report($id);
        $reportUserId = $objReport->getUserId();

        $objUser = new User();
        $userId = $objUser->fetchId();
        
        if ($reportUserId !== $userId) {
            header("Location: /");
        }
        
        $customDate = json_decode($objReport->getCustomTimeframe());
        $type = json_decode($objReport->getType());
        
        //custom fild for items, groups, clients (show or hide)
        $customFild = false;
        
        $typeOrders = "";
        
        if (array_search("orders", $type) !== false) {
            $typeOrders .= '<option value="orders" selected="">{order_all}</option>';
        } else {
            $typeOrders .= '<option value="orders">{order_all}</option>';
        }
        
        if (array_search("order_active", $type) !== false) {
            $typeOrders .= '<option value="order_active" selected="">{order_active}</option>';
        } else {
            $typeOrders .= '<option value="order_active">{order_active}</option>';
        }
        
        if (array_search("order_inactive", $type) !== false) {
            $typeOrders .= '<option value="order_inactive" selected="">{order_inactive}</option>';
        } else {
            $typeOrders .= '<option value="order_inactive">{order_inactive}</option>';
        }
        
        $typeGroups = "";
        
        if (array_search("groups_custom", $type) !== false) {
            $typeGroups .= '<option value="groups_custom" selected="">{groups_custom}</option>';
            $customFild = true;
        } else {
            $typeGroups .= '<option value="groups_custom">{groups_custom}</option>';
        }
        
        $typeItems = "";
        
        if (array_search("items_custom", $type) !== false) {
            $typeItems .= '<option value="items_custom" selected="">{items_custom}</option>';
            $customFild = true;
        } else {
            $typeItems .= '<option value="items_custom">{items_custom}</option>';
        }
        
        $typeClients = "";
        
        if (array_search("clients_orders", $type) !== false) {
            $typeClients .= '<option value="clients_orders" selected="">{user_orders}</option>';
            $customFild = true;
        } else {
            $typeClients .= 'option value="clients_orders">{user_orders}</option>';
        }
        
        if (array_search("clients_items", $type) !== false) {
            $typeClients .= '<option value="clients_items" selected="">{user_items}</option>';
            $customFild = true;
        } else {
            $typeClients .= '<<option value="clients_items">{user_items}</option>';
        }
        
        //Report vars
        $this->view->addContent('report_name', $objReport->getName());
        $this->view->addContent('report_timeframe', $objReport->getTimeframe());
        
        $this->view->addContent([
            'from_date' => $customDate->from,
            'to_date' => $customDate->to
        ]);
        
        $this->view->addContent([
            'type_orders' => $typeOrders,
            'type_groups' => $typeGroups,
            'type_items' => $typeItems,
            'type_clients' => $typeClients
        ]);
        
        $this->view->addContent('custom_fild', $objReport->getCustom());
        
        if ($customFild === true) {
            $customFildHtml = "";
        } else {
            $customFildHtml = "hidden";
        }
        $this->view->addContent('show_custom_field', $customFildHtml);
        
        $jq = "
            function chart(chartTitles, chartData) {
                $(function () {
                    $('#chartdiv').highcharts({
                        chart: {
                            type: 'column'
                        },
                        title: {
                            text: 'Chart'
                        },
                        subtitle: {
                            text: 'Source: appdev.aakasha.com'
                        },
                        xAxis: {
                            categories: chartTitles,
                            crosshair: true
                        },
                        yAxis: {
                            min: 0,
                            title: {
                                text: 'Stats'
                            }
                        },
                        plotOptions: {
                            column: {
                                pointPadding: 0.2,
                                borderWidth: 0
                            },
                            series: {
                                dataLabels: {
                                    enabled: true,
                                    format: '{point.y:.1f}'
                                }
                            }
                        },
                        series: chartData
                    });
                });
            }

            $('#from_date').datepicker({
                format: 'yyyy-mm-dd'
            });

            $('#to_date').datepicker({
                format: 'yyyy-mm-dd'
            });

            //Show custom date
            $('#timeframe').change(function() {
                var frame = $(this).find(':selected').val();

                if (frame === 'custom') {
                    $('#custom_frame').removeClass('hidden');
                } else {
                    $('#custom_frame').addClass('hidden');
                }
            });

            //Show item field
            $('#type').change(function() {
                var type = $(this).find(':selected').val();

                if ((type === 'items_custom') || (type === 'groups_custom') || (type === 'clients_orders') || (type === 'clients_items')) {
                    $('#item_field').removeClass('hidden');
                } else {
                    $('#item_field').addClass('hidden');
                }
            });

            //Generate chart
            $('#generate').click(function() {
                $.post('/reports/generate', $('#report-details').serialize(), function(data) {
                    var titles = data.titles;
                    var chartData = data.data;

                    //generate chart
                    chart(titles, chartData);
                }, 'json');
            });

            $('#type, #timeframe, #chart').chosen();
        ";

        $this->view->addContent("jq", $jq);

        //Render template
        $this->view->loadPage("reports/edit");
    }

}