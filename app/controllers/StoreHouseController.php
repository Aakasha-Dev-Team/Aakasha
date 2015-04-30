<?php namespace controllers;
use models\Authentication;
use models\Log;
use models\Products,
    models\User,
    helpers\request as Request,
    helpers\util as Util;
use helpers\Globals as Globals;
use helpers\url as URL;

/**
 * StoreHouse controller
 * @author realdark <me@borislazarov.com> on 25 Feb 2015
 */
class StoreHouseController extends \core\controller {

    public function __construct() {
        parent::__construct();
    }

    public function displayItems() {
        Authentication::chechAuthentication("exit");

        //Track last action
        User::trackLastAction();

        //Logger
        Log::logMe("Saw products table");

        $permission = Globals::get("storehouse_permissions");

        if ($permission['items']['permission'] == 0) {
            header('Location: /');
        }

        //Add information in template
        $this->view->addContent([
            "title"         => _T("Items"),
            "Home"          => _T("Home"),
            "Items"         => _T("Items"),
            "Storehouse"    => _T("Storehouse"),
            "SKU"           => _T("SKU"),
            "Group No"      => _T("Group No"),
            "Item No"       => _T("Item No"),
            "Cut No"        => _T("Cut No"),
            "Group Desc EN" => _T("Group Desc EN"),
            "Group Desc BG" => _T("Group Desc BG"),
            "Item Desc EN"  => _T("Item Desc EN"),
            "Item Desc BG"  => _T("Item Desc BG"),
            "items"         => Products::fetchAll(),
            "Save"          => _T("Save"),
            "Add row"       => _T("Add row"),
            "Image 1"       => _T("Image 1"),
            "Image 2"       => _T("Image 2"),
            'Show image'    => _T("Show image")
        ]);

        //JavaScript
        $this->view->addContent('js', "
            <script src='" . URL::get_template_path() . "js/jquery.dynatable.js'></script>\n
        ");
        
        $jq = "
            $('#add_row').click(function() {
                $('table tbody').append('\
                    <tr>\
                        <td></td>\
                        <td><input type=\"text\" class=\"form-control\" name=\"sku[]\"></td>\
                        <td><input type=\"text\" class=\"form-control\" name=\"group_no[]\"></td>\
                        <td><input type=\"text\" class=\"form-control\" name=\"item_no[]\"></td>\
                        <td><input type=\"text\" class=\"form-control\" name=\"cut_no[]\"></td>\
                        <td><input type=\"text\" class=\"form-control\" name=\"group_description_en[]\"></td>\
                        <td><input type=\"text\" class=\"form-control\" name=\"group_description_bg[]\"></td>\
                        <td><input type=\"text\" class=\"form-control\" name=\"item_description_en[]\"></td>\
                        <td><input type=\"text\" class=\"form-control\" name=\"item_description_bg[]\"></td>\
                        <td><input type=\"text\" class=\"form-control\" name=\"image_1[]\"></td>\
                        <td><input type=\"text\" class=\"form-control\" name=\"image_2[]\"></td>\
                        <td></td>\
                        <td class=\"delete-unsaved-row-js\"><i class=\"icon icon-trash\"></i></td>\
                    </tr>\
                ');
            });

            $('table tbody').on('click', '.delete-unsaved-row-js', function() {
                $(this).parent().remove();
            });

            $('#create_save').click(function() {
                var sku                  = $('input[name=\'sku\\[\\]\']').map(function(){return $(this).val();}).get();
                var group_no             = $('input[name=\'group_no\\[\\]\']').map(function(){return $(this).val();}).get();
                var item_no              = $('input[name=\'item_no\\[\\]\']').map(function(){return $(this).val();}).get();
                var cut_no               = $('input[name=\'cut_no\\[\\]\']').map(function(){return $(this).val();}).get();
                var group_description_en = $('input[name=\'group_description_en\\[\\]\']').map(function(){return $(this).val();}).get();
                var group_description_bg = $('input[name=\'group_description_bg\\[\\]\']').map(function(){return $(this).val();}).get();
                var item_description_en  = $('input[name=\'item_description_en\\[\\]\']').map(function(){return $(this).val();}).get();
                var item_description_bg  = $('input[name=\'item_description_bg\\[\\]\']').map(function(){return $(this).val();}).get();
                var image_1              = $('input[name=\'image_1\\[\\]\']').map(function(){return $(this).val();}).get();
                var image_2              = $('input[name=\'image_2\\[\\]\']').map(function(){return $(this).val();}).get();

                $.post('/storehouse/new_item_save', {
                    sku                 : sku,
                    item_no             : item_no,
                    group_no            : group_no,
                    cut_no              : cut_no,
                    group_description_en: group_description_en,
                    group_description_bg: group_description_bg,
                    item_description_en : item_description_en,
                    item_description_bg : item_description_bg,
                    image_1             : image_1,
                    image_2             : image_2
                }, function(data) {
                    if (data.status == true) {
                        location.reload();
                    }
                }, 'json');
            });

            $('#table-fill').on('click', 'tbody tr .delete-saved-row-js', function() {
                var c = confirm('Delete row?');
                var id = $(this).data('id'); // get item row

                if(c === true) {
                    $(this).parent().parent().remove(); // delete row
                    $.post('/storehouse/existing_item_delete', {id: id});
                }
            });

            $('#table-fill').on('click', 'td a', function() {
                var image_url = $(this).data('image-url');
                $('#imgModal img').attr('src', image_url);
            });

            $('#table-fill').dynatable();
        ";
        
        $this->view->addContent("jq", $jq);
        
        //Render template
        $this->view->loadPage("storehouse/items");
    }
    
    /**
     * Save new product
     * @author realdark <me@borislazarov.com> on 27 Feb 2015
     * @return void
     */
    public function createSave() {
        //Track last action
        User::trackLastAction();
        
        //Util::modal(true, _T("Success"), _T("Your comment was added."));
        
        $sku                  = Request::get("sku", "array");
        $group_no             = Request::get("group_no", "array");
        $item_no              = Request::get("item_no", "array");
        $cut_no               = Request::get("cut_no", "array");
        $group_description_en = Request::get("group_description_en", "array");
        $group_description_bg = Request::get("group_description_bg", "array");
        $item_description_en  = Request::get("item_description_en", "array");
        $item_description_bg  = Request::get("item_description_bg", "array");
        $image_1              = Request::get("image_1", "array");
        $image_2              = Request::get("image_2", "array");

        //Logger
        Log::logMe("Added new product with sku " . $sku);
        
        $totalRecords = count($sku) - 1;
        
        for($i=0; $i<=$totalRecords; $i++) {
            $objProducts = new Products();
            $objProducts->setSku($sku[$i]);
            $objProducts->setGroupNo($group_no[$i]);
            $objProducts->setItemNo($item_no[$i]);
            $objProducts->setCutNo($cut_no[$i]);
            $objProducts->setGroupDescriptionEn($group_description_en[$i]);
            $objProducts->setGroupDescriptionBg($group_description_bg[$i]);
            $objProducts->setItemDescriptionEn($item_description_en[$i]);
            $objProducts->setItemDescriptionBg($item_description_bg[$i]);
            $objProducts->image_1 = $image_1[$i];
            $objProducts->image_2 = $image_2[$i];
            
            try {
                $objProducts->save();
            } catch (\Exception $e) {
                \core\logger::exception_handler($e);
            }
        }
        
        Util::modal(true, _T("Succes"), _T("Done"));
    }
    
    /**
     * Delete existing record
     * @author realdark <me@borislazarov.com> on 27 Feb 2015
     * @return void
     */
    public function doDelete() {
        if (!empty($_POST['id'])) {

            //Logger
            Log::logMe("Deleted product with id " . $_POST['id']);

            $objProducts = new Products($_POST['id']);
            $objProducts->delete();
        }
    }

    /**
     * Display edit
     */
    public function edit($id) {
        Authentication::chechAuthentication("exit");

        //Track last action
        User::trackLastAction();

        $permission = Globals::get("storehouse_permissions");

        if ($permission['items']['permission'] == 0) {
            header('Location: /');
        }

        //Add information in template
        $this->view->addContent([
            "title"         => _T("Items"),
            "Home"          => _T("Home"),
            "Edit"          => _T("Edit"),
            "Storehouse"    => _T("Storehouse"),
            "SKU"           => _T("SKU"),
            "Group No"      => _T("Group No"),
            "Item No"       => _T("Item No"),
            "Cut No"        => _T("Cut No"),
            "Group Desc EN" => _T("Group Desc EN"),
            "Group Desc BG" => _T("Group Desc BG"),
            "Item Desc EN"  => _T("Item Desc EN"),
            "Item Desc BG"  => _T("Item Desc BG"),
            "Image 1"       => _T("Image 1"),
            "Image 2"       => _T("Image 2"),
            "Save"          => _T("Save"),
            "Cancel"        => _T("Cancel")
        ]);


        //Load product
        $objProducts = new Products($id);

        $this->view->addContent([
            'id'                => $id,
            "SKU val"           => $objProducts->getSku(),
            "Group No val"      => $objProducts->getGroupNo(),
            "Item No val"       => $objProducts->getItemNo(),
            "Cut No val"        => $objProducts->getCutNo(),
            "Group Desc EN val" => $objProducts->getGroupDescriptionEn(),
            "Group Desc BG val" => $objProducts->getGroupDescriptionBg(),
            "Item Desc EN val"  => $objProducts->getItemDescriptionEn(),
            "Item Desc BG val"  => $objProducts->getItemDescriptionBg(),
            "Image 1 val"       => $objProducts->image_1,
            "Image 2 val"       => $objProducts->image_2
        ]);

        //Render template
        $this->view->loadPage("storehouse/edit");

    }
    
    /**
     * Edit record
     * @author realdark <me@borislazarov.com> on 27 Feb 2015
     * @return void
     */
    public function doEdit() {
        if (!empty($_POST['id'])) {
            $sku                  = Request::get("sku", "string");
            $group_no             = Request::get("group_no", "integer");
            $item_no              = Request::get("item_no", "integer");
            $cut_no               = Request::get("cut_no", "string");
            $group_description_en = Request::get("group_desc_en", "string");
            $group_description_bg = Request::get("group_desc_bg", "string");
            $item_description_en  = Request::get("item_desc_en", "string");
            $item_description_bg  = Request::get("item_desc_bg", "string");
            $image_1              = Request::get("image_1", "string");
            $image_2              = Request::get("image_2", "string");

            //Logger
            Log::logMe("Edited product with sku " . $sku);
            
            $objProducts = new Products($_POST['id']);
            $objProducts->setSku($sku);
            $objProducts->setGroupNo($group_no);
            $objProducts->setItemNo($item_no);
            $objProducts->setCutNo($cut_no);
            $objProducts->setGroupDescriptionEn($group_description_en);
            $objProducts->setGroupDescriptionBg($group_description_bg);
            $objProducts->setItemDescriptionEn($item_description_en);
            $objProducts->setItemDescriptionBg($item_description_bg);
            $objProducts->image_1 = $image_1;
            $objProducts->image_2 = $image_2;
            
            try {
                $objProducts->save();
            } catch (\Exception $e) {
                \core\logger::exception_handler($e);
            }

            \helpers\url::redirect('storehouse/display_items');
        }
    }

    /**
     * Items stats
     */
    public function stats() {
        Authentication::chechAuthentication("exit");

        //Track last action
        User::trackLastAction();

        $permission = Globals::get("storehouse_permissions");

        if ($permission['items']['permission'] == 0) {
            header('Location: /');
        }

        //Add information in template
        $this->view->addContent([
            "title"         => _T("Stats"),
            "Home"          => _T("Home"),
            "Stats"         => _T("Stats"),
            "Storehouse"    => _T("Storehouse")
        ]);

        $jq = '
                    var chart;

            var chartData = [
                {
                    "country": "USA",
                    "visits": 4025
                },
                {
                    "country": "China",
                    "visits": 1882
                },
                {
                    "country": "Japan",
                    "visits": 1809
                },
                {
                    "country": "Germany",
                    "visits": 1322
                },
                {
                    "country": "UK",
                    "visits": 1122
                },
                {
                    "country": "France",
                    "visits": 1114
                },
                {
                    "country": "India",
                    "visits": 984
                },
                {
                    "country": "Spain",
                    "visits": 711
                },
                {
                    "country": "Netherlands",
                    "visits": 665
                },
                {
                    "country": "Russia",
                    "visits": 580
                },
                {
                    "country": "South Korea",
                    "visits": 443
                },
                {
                    "country": "Canada",
                    "visits": 441
                },
                {
                    "country": "Brazil",
                    "visits": 395
                },
                {
                    "country": "Italy",
                    "visits": 386
                },
                {
                    "country": "Australia",
                    "visits": 384
                },
                {
                    "country": "Taiwan",
                    "visits": 338
                },
                {
                    "country": "Poland",
                    "visits": 328
                }
            ];


            AmCharts.ready(function () {
                // SERIAL CHART
                chart = new AmCharts.AmSerialChart();
                chart.dataProvider = chartData;
                chart.categoryField = "country";
                chart.startDuration = 1;

                // AXES
                // category
                var categoryAxis = chart.categoryAxis;
                categoryAxis.labelRotation = 90;
                categoryAxis.gridPosition = "start";

                // value
                // in case you don\'t want to change default settings of value axis,
                // you don\'t need to create it, as one value axis is created automatically.

                // GRAPH
                var graph = new AmCharts.AmGraph();
                graph.valueField = "visits";
                graph.balloonText = "[[category]]: <b>[[value]]</b>";
                graph.type = "column";
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.8;
                chart.addGraph(graph);

                                // EXPORT
                chart.amExport = {};

                // Advanced configuration
                chart.amExport.userCFG = {
                    menuTop	    : "24px",
                    menuLeft	: "auto",
                    menuRight	: "24px",
                    menuBottom	: "10px",
                    menuItems	: [{
                        textAlign : "right",
                        icon      : "cdnjs.cloudflare.com/ajax/libs/amcharts/3.13.0/images/export.png",
                        iconTitle : "Save chart as an image",
                        onclick   : function () {},
                        items     : [{
                            title: "Download JPG image",
                            format: "jpg"
                            }, {
                            title: "Download PNG image",
                            format: "png"
                            }, {
                            title: "Download SVG vector image",
                            format: "svg"
                        }]
                    }]
                }

                // CURSOR
                var chartCursor = new AmCharts.ChartCursor();
                chartCursor.cursorAlpha = 0;
                chartCursor.zoomable = false;
                chartCursor.categoryBalloonEnabled = false;
                chart.addChartCursor(chartCursor);

                chart.creditsPosition = "top-left";

                chart.write("chartdiv");
            });
        ';

        $this->view->addContent("jq", $jq);

        //Render template
        $this->view->loadPage("storehouse/stats");
    }
}