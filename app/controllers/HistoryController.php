<?php namespace controllers;
use helpers\request as Request,
    helpers\Globals as Globals,
    models\Order as Order,
    models\User as User,
    models\InvoiceHistory as InvoiceHistory,
    models\Authentication as Authentication;
use models\Log;

/**
 * History controller
 * @author realdark <me@borislazarov.com> on 28 Dec 2014
 */
class HistoryController extends \core\controller {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Display generated invoices
     * @author realdark <me@borislazarov.com> on 28 Dec 2014
     * @return array
     */
    public function displayInvoices() {
        //If user is not logged send him to loggin page
        Authentication::chechAuthentication("exit");
        
        //Track last action
        User::trackLastAction();
        
        //permissions
        $permission = Globals::get("history_permissions");
        
        if ($permission['invoices_history']['permission'] == 0) {
            header('Location: /');
        }
        
        //add content
        $this->view->addContent([
            "title"                          => _T("Invoices History"),
            "Warning!"                       => _T("Warning!"),
            "There is no such order number." => _T("There is no such order number."),
            "Enter the order number"         => _T("Enter the order number"),
            "View"                           => _T("View"),
            "Adress"                         => _T("Adress"),
            "Created"                        => _T("Created"),
            "Invoices"                       => _T("Invoices"),
            "History"                        => _T("History"),
            "History invoices"               => _T("History invoices"),
            "Show generated invoices"        => _T("Show generated invoices")
        ]);
        
        //jquery
        $jq = "
            //hide aler msg
            $('#show-id-error').hide();
        
            //check order existing
            $('#order_number').keyup(function(){
                var id = $(this).val();
            
                $.post('/orders/check_order_ajax', {
                    id : id
                }, function(data) {
                    
                    //if found a error display msg
                    if (data.error == false) {
                        $('#show-id-error').hide();
                        $('.fix-btn-js').css('margin-top', '23px');
                    } else {
                        $('#show-id-error').show();
                        $('.fix-btn-js').css('margin-top', '-6px');
                    }
                }, 'json');
            });
            
            //fetch orders
            $('#view').click(function(event) {
                event.preventDefault();
                var id = $('#order_number').val();
                load.show();
                
                $.post('/history/display_invoices_ajax', {id: id}, function(data) {
                    load.hide();
                    
                    if (data.length > 0) {
                        var table = '';
                        
                        //show created invoices
                        $('.display-invoices-history').removeClass('hidden');
                        
                        //clear old data
                        $('.display-invoices-history table tbody').empty();
                        
                        //create invoce table row
                        $.each( data, function( key, value ) {
                            table += '\
                            <tr>\
                                <td>' + value.adress + '</td>\
                                <td>' + value.created + '</td>\
                            </tr>\
                            ';
                        });
                        
                        //
                        $('.display-invoices-history table tbody').html(table);
                        
                    } else {
                        //clear old data
                        $('.display-invoices-history table tbody').empty();
                        
                        //display warning
                        modal('" . _T("Warning") . "', '" . _T("There is no created invoices for this order!") . "');
                    }
                    
                }, 'json');
            });
        ";
        
        //add jqruery to page
        $this->view->addContent("jq", $jq);
        
        //render template
        $this->view->loadPage("history/display_invoices");
    }
    
    /**
     * Fetch invoices
     * @author realdark <me@borislazarov.com> on 28 Dec 2014
     * @return json
     */
    public function displayInvoicesAjax() {
        //Track last action
        User::trackLastAction();

        //Logger
        Log::logMe("Saw invoices history");
        
        $saleId = Request::get("id", "integer");
        
        $objOrder = new Order(["sale" => $saleId], ["id"]);
        $orderId = $objOrder->getId();
        
        $objInvoicesHistory = new InvoiceHistory();
        $invoices = $objInvoicesHistory->fetchInvoices($orderId);
        
        echo json_encode($invoices);
    }
}