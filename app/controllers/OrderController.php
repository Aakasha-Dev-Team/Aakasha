<?php namespace controllers;
use helpers\util as Util,
    helpers\request as Request,
    helpers\url as URL,
    helpers\Globals as Globals,
    libraries\phpimageworkshop\ImageWorkshop as ImageWorkshop,
    models\Order as Order,
    models\ShoppingCart as ShoppingCart,
    models\Customer as Customer,
    models\InvoiceCompanyInformation as InvoiceCompanyInformation,
    models\Authentication as Authentication,
    models\User as User,
    models\InvoiceHistory as InvoiceHistory,
    models\Products;
use models\Feedback;
use models\Log;
use models\OrderCheck;
use models\OrderHistory;
use models\ProductProgress;
use models\Task;

/**
 * Order controller
 * @author realdark <me@borislazarov.com>
 */
class OrderController extends \core\controller {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Upload file through ajax
     * @author Bobi <b.lazarov@aakasha.com> on 2 Dec 2014
     * @return string
     */
    public function index() {
        //If user is not logged send him to loggin page
        Authentication::chechAuthentication("exit");
        
        //Track last action
        User::trackLastAction();
        
        //permissions
        $permission = Globals::get("orders_permissions");
        
        if ($permission['upload_order']['permission'] == 0) {
            header('Location: /');
        }
        
        //Add information in template
        $this->view->addContent([
            "title"          => _T("Orders"),
            "Import"         => _T("Import"),
            "Export"         => _T("Export"),
            "Orders listing" => _T("Orders listing"),
            "File input"     => _T("File input"),
            "Submit"         => _T("Submit"),
            "Show Details"   => _T("Show Details"),
            "Upload files"   => _T("Upload files"),
            "Upload orders file" => _T("Upload orders file")
        ]);

        $lastProduct = Order::enableBtn();

        if ($lastProduct === true) {
            $this->view->addContent('submit_btn_status', '');
        } else {
            $this->view->addContent('submit_btn_status', 'disabled');
        }
        
        //jQuery
        $jq = "
            //disable submit btn on click
            $('#upload-file').click(function() {
                $(this).prop('disabled', true);
            });

            //enable submit btn on interval
            setInterval(function(){  $('#upload-file').prop('disabled', false); }, 300000);

            document.getElementById('file').onchange = function () {
                document.getElementById('uploadFile').value = this.value;
            };
            
            //hide show details btn
            $('#show-details').hide();
            $('#response').hide();
            
            // Variable to store your files
            var files;
             
            // Add events
            $('#file').on('change', prepareUpload);
             
            // Grab the files and set them to our variable
            function prepareUpload(event) {
                files = event.target.files;
            }
            
            //Sent file through ajax to uploadFileAjax() php function
            $('form').on('submit', uploadFiles);
             
            // Catch the form submit and upload the files
            function uploadFiles(event) {
                event.stopPropagation(); // Stop stuff happening
                event.preventDefault(); // Totally stop stuff happening
             
                // START A LOADING SPINNER HERE
                load.show();
             
                // Create a formdata object and add the files
                var data = new FormData();
                
                $.each(files, function(key, value) {
                    data.append(key, value);
                });
                
                $.ajax({
                    url: '/orders/upload_ajax',
                    type: 'POST',
                    data: data,
                    cache: false,
                    processData: false, // Don't process the files
                    contentType: false, // Set content type to false as jQuery will tell the server its a query string request
                    success: function(data, textStatus, jqXHR) {
                        load.hide();
                        var obj = $.parseJSON(data);

                        if (obj.status == 'warning') {
                            modal(obj.title, obj.body, 'warning');

                            $('#show-details').show();
                            $('#response').html(obj.show_details);
                        } else if (obj.status == false) {
                            modal(obj.title, obj.body, 'error');
                        } else {
                            modal(obj.title, obj.body);
                        }

                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        load.hide();
                        console.log('ERRORS: ' + textStatus);
                    }
                });
            }
            
            //simulate click for upload
            $('#dummy_click').click(function(){
                $('#file').click();
            });
            
            //Show details page
            $('#show-details').click(function(event) {
                event.preventDefault();
                $('#show-details').hide();
                $('#response').show();
            });
            
            //add file input class
            $('#uploadFile').addClass('form-control');
            
        ";
        
        //Add information in template
        $this->view->addContent("jq", $jq);
        
        //Render template
        $this->view->loadPage("orders/index");
    }
    
    /**
     * Upload file through ajax
     * @author Bobi <b.lazarov@aakasha.com> on 4 Dec 2014
     * @return string
     */
    public function uploadFileAjax() {
        //If user is not logged send him to loggin page
        Authentication::chechAuthentication("exit");
        
        //Track last action
        User::trackLastAction();


        //Logger
        Log::logMe("Uploaded file with orders");
        
        $objOrder = new Order();
        
        //Upload file to server
        $file     = $objOrder->uploadFile($_FILES);
        
        //Check file status
        if ($file == true) {
            $status = $objOrder->importOrderToDB();
            
            //If file is string there is some error otherwise is ok
            if (is_string($status)) {
                $data = json_encode([
                    "status"       => 'warning',
                    "title"        => _T("Warning"),
                    "body"         => _T("The file was successfully uploaded but some of the items don`t have SKU. For more information click on details button!."),
                    "show_details" => $status
                    ]);

                /** @var $data TYPE_NAME */
                echo $data;
            } else {
                Util::modal(true, _T("Success"), _T("The file was successfully uploaded."));
            }
        } else {
            //File was not uploaded msg
            Util::modal(false, _T("Error"), _T("The file was NOT uploaded."));
        }
    }

    /**
     * Upload file through ajax
     * @author Bobi <b.lazarov@aakasha.com> on 4 Dec 2014
     * @param $slug
     * @return string
     */
    public function generateInvoice($slug) {
        //If user is not logged send him to loggin page
        Authentication::chechAuthentication("exit");
        
        //Track last action
        User::trackLastAction();
        
        //permissions
        $permission = Globals::get("invoices_permissions");
        
        if ($permission['generate']['permission'] == 0) {
            header('Location: /');
        }
        
        if ($slug != 0) {
            $saleId = $slug;
        } else {
            $saleId = 0;
        }
        
        //Add information in template
        $this->view->addContent([
            "title"                            => _T("Generate"),
            "Generate Invoice"                 => _T("Generate Invoice"),
            "Generate order"                   => _T("Generate order"),
            "Generade orders list"             => _T("Generade orders list"),
            "Close"                            => _T("Close"),
            "Warning!"                         => _T("Warning!"),
            "There is no such order number."   => _T("There is no such order number."),
            "Enter the order number"           => _T("Enter the order number"),
            "Select the number of packages"    => _T("Select the number of packages"),
            "Enter the weight of the packages" => _T("Enter the weight of the packages"),
            "Generate invoice"                 => _T("Generate invoice"),
            "Total Value of Shipment"          => _T("Total Value of Shipment"),
            "Invoice generation"               => _T("Invoice generation"),
            "Home"                             => _T("Home"),
            "Invoices"                         => _T("Invoices")
        ]);
        
        //jQuery
        $jq = "
            //hide aler msg
            $('#show-id-error').hide();
            
            //sale id
            var sale_id = " . $saleId . ";
            
            //show and hide close btn
            if (sale_id != 0) {
                $('#order_number').val(sale_id);
                $('#close').show();
            } else {
                $('#close').hide();
            }
            
            //close window
            $('#close').click(function() {
                window.close();
            });
            
            //check order existing
            $('#order_number').change(function(){
                var id = $(this).val();
            
                $.post('/orders/check_order_ajax', {
                    id : id
                }, function(data) {
                    
                    //if found a error display msg
                    if (data.error == false) {
                        $('#show-id-error').hide();
                    } else {
                        $('#show-id-error').show();
                    }
                }, 'json');
            });
            
            //Calculate shipping rate
            $('#weight').keyup(function(){
                var weight = $(this).val();
            
                $.post('/orders/calculate_shipping_ajax', {
                    weight : parseFloat(weight)
                }, function(data) {
                    
                    $('#value_shipment').val(data);
                    
                });
            });
            
            //activate custom shipping
            $('.checkbox_orders').click(function(){
                var check_box_status = $(this).prop('checked');
                
                if (check_box_status == true) {
                    $('#value_shipment').prop('disabled', false);
                    $('#value_shipment').css('background-color', 'white');
                } else {
                    $('#value_shipment').prop('disabled', true);
                    $('#value_shipment').css('background-color', '#ddd');
                }
            });
            
            //generate and download invoice
            $('#generate').click(function(){
                $('#value_shipment').prop('disabled', false);
                
                var formData   = $('form').serialize();
                var invoice_id = $('#order_number').val();
                
                $('#value_shipment').prop('disabled', true);
                
                //Display wait mouse
                load.show();
                
                $.post('/orders/generate_invoice_ajax', formData, function(data) {
                    //Return mouse to normal
                    load.hide();
                    //activate auto download js function
                    file.download(data.file, 'invoice_' + invoice_id + '.pdf');
                }, 'json');
            });
        ";
        
        //Add information in template
        $this->view->addContent("jq", $jq);
        
        //Render Template
        $this->view->loadPage("orders/generate_invoice");
    }
    
    /**
     * Check the existence of order
     * @author Bobi <me@borislazarov.com> on 5 Dec 2014
     * @return boolean
     */
    public function checkOrderAjax() {
        //If user is not logged send him to loggin page
        Authentication::chechAuthentication("exit");
        
        //Track last action
        User::trackLastAction();
        
        //Order id
        $id = Request::get("id", "integer");
        
        $objOrder = new Order(["sale" => $id], ["id"]);
        $orderId = $objOrder->getId();
        
        if (isset($orderId)) {
            $error['error'] = false;
        } else {
            $error['error'] = true;
        }
        
        echo json_encode($error);
    }
    
    /**
     * Calculate shipping
     * @author Bobi <me@borislazarov.com> on 8 Dec 2014
     * @return string
     */
    public function calculateShippingAjax() {
        Authentication::chechAuthentication("exit");
        
        //Track last action
        User::trackLastAction();
        
        $objShoppingCart = new ShoppingCart();
        $shippingRtes    = $objShoppingCart->fetchRates();
        
        $weight = Request::get("weight", "float");
       
        foreach ($shippingRtes as $rate) {
            $from = $rate->getFromWeight();
            $to   = $rate->getToWeight();
            
            if (!isset($from)) {
                $from = 0;
            }
            
            if (!isset($to)) {
                $to = INF;
            }
            
            if (($weight > $from) && ($weight <= $to)) {
                echo $rate->getPrice();
                break;
            }
        }
    }
    
    private function invoiceTemplate($values = []) {
        Authentication::chechAuthentication("exit");
        
        //Track last action
        User::trackLastAction();
        
        $invoice = "
         <style>
             h5 {
                font-size: 1.5em;
             }
            table {
                border-spacing: 0px;
                border-collapse: collapse;
                width: 100%;
                font-size: 14px;
                line-height: 22px;
            }
            td,tr,table {
                border: 1px solid black;
            }

            td {
                height: 28px;
                padding: 5px;
            }
            .footer table {
                border:none;
            }
            .footer td {
                border:none;
            }
            .footer tr {
                border: none;
            }
            .italic {
                font-style: italic;
            }
            table_footer {
                border 1px solid #fff;
            }
        </style>
        <br><br>
        <h5><strong>Your name:         {$values['from']['accountable_person']}</strong></h5>
        <h5><strong>Compnay:           {$values['from']['company_name']}</strong></h5>
        <h5><strong>Street address:    {$values['from']['adress']}</strong></h5>
        <h5><strong>Postal Code, City: {$values['from']['pc_city']}</strong></h5>
        <h5><strong>Country:           {$values['from']['country']}</strong></h5>
        <h5><strong>Telephone:         Telex/Fax No: {$values['from']['contact']}</strong></h5>
        <hr/></br>
        <h5><i>Ship to:</i></h5>
        <h5><strong>Company:          {$values['to']['client']}</strong></h5>
        <h5><strong>Street address:   {$values['to']['adress']}</strong></h5>
        <h5><strong>Postal Code,City: {$values['to']['pc_city']}</strong></h5>
        <h5><strong>Country:          {$values['to']['country']}</strong></h5>
        <h5><strong>Contact:          </strong></h5>
        <h5><strong>Telephone:       {$values['to']['contact']}</strong></h5>
        

        <table>
                <tr class='header'>
                    <td style=\"width:40px; font-size:12px;\"><strong>No.<br>Units</strong></td>
                    <td style=\"width:220px; font-size:12px;\"><strong>Description of Goods</strong></td>
                    <td style=\"width:120px; font-size:12px;\"><strong>Country of origin</strong></td>
                    <td style=\"width:80px; font-size:12px;\"><strong>Unit Value</strong></td>
                    <td style=\"width:80px; font-size:12px;\"><strong>Total Value</strong></td>
                </tr>
                <tr style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\">
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\">1</td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\">Clothes</td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\">Bulgaria</td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\">1</td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\">1</td>
                </tr>
                <tr style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\">
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                </tr>
                <tr style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\">
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                </tr>
                <tr style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\">
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                </tr>
                <tr style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\">
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                </tr>
                <tr style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\">
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                </tr>
                <tr style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\">
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"><strong>&nbsp;&nbsp;Total value of shipment:</strong></td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\">&nbsp;{$values['invoice']['shipping']}$&nbsp;&nbsp;</td>
                </tr>
                <tr style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\">
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"><strong>&nbsp;&nbsp;Number of packages:</strong></td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\">&nbsp;{$values['invoice']['packages']}&nbsp;&nbsp;</td>
                </tr>
                <tr style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\">
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"><strong>&nbsp;&nbsp;Weight:</strong></td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\"></td>
                    <td style=\"padding-bottom:84px; font-size:12px; padding-top:8px;\">&nbsp;{$values['invoice']['weight']}&nbsp;&nbsp;</td>
                </tr>
        </table>

        <div class='footer'>
                <h5><strong>NO COMMERCIAL VALUE. VALUE FOR CUSTOMS PURPOSES ONLY</strong></h5>
                Signature___________________________ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Date &nbsp;&nbsp;&nbsp;&nbsp;{$values['invoice']['curr_date']}
                
        </div>
        ";
        
        return $invoice;
    }
    
    /**
     * Generate invoice
     * @author Bobi <me@borislazarov.com> on 8 Dec 2014
     * @return json
     */
    public function generateInvoiceAjax() {
        Authentication::chechAuthentication("exit");
        
        //Track last action
        User::trackLastAction();

        //Logger
        Log::logMe("Generated invoice for order " . Request::get("order_number", "integer"));
        
        include_once(APP_PATH . "libraries/tcpdf/tcpdf.php");
        
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        $orderNumber = Request::get("order_number", "integer");
        $packages    = Request::get("packages", "integer");
        $weight      = Request::get("weight", "float");
        $totalValue  = Request::get("total_value", "float");
        
        // set document information
        $pdf->SetAuthor('Aakasha Ltd');
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(10);
        
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE);
        
        // set font
        $pdf->SetFont('helvetica', 'B', 20);
        
        // add a page
        $pdf->AddPage();
        
        $pdf->Write(0, 'Invoice', '', 0, 'L', true, 0, false, false, 0);
        
        $pdf->SetFont('helvetica', '', 8);
        
        //invoice var
        $invoice = [];

        //begin company information
        $objCompanyInfo                        = new InvoiceCompanyInformation(2);
        
        $companyAdress = explode(",", $objCompanyInfo->getAdress());
        
        $invoice['from']['accountable_person'] = $objCompanyInfo->getAccountablePerson();
        $invoice['from']['company_name']       = $objCompanyInfo->getCompanyName();
        $invoice['from']['adress']             = trim($companyAdress[0]);
        $invoice['from']['pc_city']            = trim($companyAdress[1]);
        $invoice['from']['country']            = $objCompanyInfo->getCountry();
        $invoice['from']['contact']            = $objCompanyInfo->getContact();
        //end
        
        //begin client information
        $objOrder = new Order(["sale" => $orderNumber], ["id"]);
        
        if ($objOrder != false) {
            $orderId = $objOrder->getId();
        }
        //end
        
        //begin fetch customer
        if ($objOrder != false) {
            
            $objCustomer = new Customer(["order_id" => $orderId]);
            
            $customerInformation = explode("\n", $objCustomer->getShippingAdress());
            
            $invoice['to']['client']  = $customerInformation[0];
            $invoice['to']['adress']  = $customerInformation[1];
            $invoice['to']['pc_city'] = $customerInformation[2];
            $invoice['to']['country'] = $customerInformation[3];
            $invoice['to']['contact'] = NULL;
            
        }
        //end
        
        //invoice information
        if ($objOrder != false) {
            $invoice['invoice']['shipping'] = $totalValue;
            $invoice['invoice']['packages'] = $packages;
            $invoice['invoice']['weight']   = $weight;
            
            $dateTime = new \DateTime();
            
            $invoice['invoice']['curr_date'] = $dateTime->format("d.m.Y");
        }
        //end
        
        if ($objOrder != false) {
            
            //generate table
            $tbl = $this->invoiceTemplate($invoice);
            
            //write table
            $pdf->writeHTML($tbl, true, false, true, false, '');
            
            $uniqId = uniqid();
            $file   = ROOT_PATH . 'uploads/orders/invoices/' . $uniqId . ".pdf";
            
            try {
                //save to file
                $pdf->Output($file, 'F');
            } catch (\Exception $e) {
                $this->logger->logEvent($e->getMessage());
            }
            
            $data['file'] = DIR . 'uploads/orders/invoices/' . $uniqId . ".pdf";
            echo json_encode($data);
        }
        
        //save to invoice history
        $objInvoiceHistory = new InvoiceHistory();
        $objInvoiceHistory->setOrderId($orderId);
        $objInvoiceHistory->setAdress($objCustomer->getShippingAdress());
        $objInvoiceHistory->set_expr('created', 'NOW()');
        $objInvoiceHistory->save();
    }
    
    /**
     * Fetch orders
     * @author Bobi <me@borislazarov.com> on 9 Dec 2014
     * @return string
     */
    public function displayOrders() {
        
        //Chech Authentication
        Authentication::chechAuthentication("exit");
        
        //Track last action
        User::trackLastAction();

        //Logger
        Log::logMe("Saw orders");
        
        //permissions
        $permission         = Globals::get("orders_permissions");
        $permissionInvoices = Globals::get("invoices_permissions");
        
        if (($permission['see_orders']['permission'] == 0) && ($permissionInvoices['show_orders']['permission'] == 0)) {
            header('Location: /');
        }
        
        //$hash = \libraries\password::make("scott123");
        //var_dump($hash);
        
        $this->view->addContent([
            "title"          => _T("Display Orders"),
            "Import"         => _T("Import"),
            "Export"         => _T("Export"),
            "Orders listing" => _T("Orders listing"),
            "from date"      => _T("from date"),
            "to date"        => _T("to date"),
            "Search"         => _T("Search"),
            "Order number"   => _T("Order number"),
            "Date"           => _T("Date"),
            "Progress"       => _T("Time limit"),
            "Actions"        => _T("Actions"),
            "Status"         => _T("Status"),
            "Active"         => _T("Active"),
            "Completed"      => _T("Completed"),
            "All"            => _T("All"),
            "Yes"            => _T("Yes"),
            "No"             => _T("No"),
            "Standart"       => _T("Standart"),
            "Show orders"    => _T("Show orders"),
            "orders type"    => _T("Orders type"),
            "orders status"  => _T("Orders status"),
            "Home"           => _T("Home"),
            "Invoices"       => _T("Orders"),
            "New"            => _T("New"),
            "In production"  => _T("In production"),
            "For packaging"  => _T("For packaging"),
            "Packed"         => _T("Packed"),
            "Invoice created"=> _T("Invoice created"),
            "Shipped"        => _T("Shipped"),
            "Returned"       => _T("Returned")
        ]);
        
        $dt = new \DateTime();
        
        $today = getdate();
        
        if ($today["weekday"] == "Monday") {
            $dt->modify('-1 day');
            $yesterday["end"]   = $dt->format('Y-m-d');
            
            $dt->modify('-2 day');
            
            $yesterday["start"] = $dt->format('Y-m-d');
        } else {
            $dt->modify('-1 day');
            
            $yesterday = [
                "start" => $dt->format('Y-m-d'),
                "end"   => $dt->format('Y-m-d')
            ];
        }
        
        //add default date
        $this->view->addContent([
            "start_date" => $yesterday['start'],
            "end_date"  => $yesterday['end'],
        ]);
        
        //JavaScript
        $this->view->addContent('js', "
            <script src='" . URL::get_template_path() . "js/jquery.dynatable.js'></script>\n
            <script src='" . URL::get_template_path() . "js/datetime.js'></script>
        ");
        
        //edit order permissions
        if ($permission['edit_order']['edit'] == 1) {
            $permissionComments = "edit";
        } else {
            $permissionComments = "view";
        }

        //Fetch Users
        $users = new User();
        $arrUsers = $users->fetchUsers();
        $checkedBy = "<option value=\'\'></option>";

        foreach ($arrUsers as $user) {
            $checkedBy .= "<option value=\'" . $user['id'] . "\'>" . _T($user['name']) . "</option>";
        }

        //jQuery
        $jq = "
        
            //orders id
            var order_ids = [];

            //users :: oprions
            var user_options = '" . $checkedBy . "';
            
            $(function() {
              $('#from_date').datepicker({
                format: 'yyyy-mm-dd'
              });
            });
            
            $(function() {
              $('#to_date').datepicker({
                format: 'yyyy-mm-dd'
              });
            });
            
            function showDetails(id, action) {
                if (action == 'display') {
                    
                    //hide table
                    $('#table-response').hide();
                    $('.orders_dates').hide();
                
                    //fetch order
                    $.post('/orders/fetch_order_ajax', {
                        id   : id
                    }, function(data) {
                    
                        //Comment permission
                        var comment_permission = '" . $permissionComments . "';
                        var comment_checkbox_buyer  = '';
                        var comment_checkbox_seller = '';
                        var new_adress_checkbox     = '';   
                        
                        if (comment_permission == 'edit') {
                            comment_checkbox_buyer  = '<input id=\"buyer_checkbox\"type=\"checkbox\" for=\"buyer_notes\">';
                            comment_checkbox_seller = '<input id=\"seller_checkbox\"type=\"checkbox\" for=\"seller_notes\">';
                            new_adress_checkbox     = '<input id=\"new_adress_checkbox\"type=\"checkbox\" for=\"newadress_notes\">';
                        }
                        
                        //Scheduled to ship date
                        var scheduled_date_arr   = data[0][0].scheduled_to_ship_date.split(' ');
                        var scheduled_date_split = scheduled_date_arr[0].split('-');
                        
                        var scheduled_myDate = new Date(scheduled_date_split[0], scheduled_date_split[1] - 1, scheduled_date_split[2]);
                        var scheduled_date   = scheduled_myDate.format('M jS, Y');
                        
                        //Order created
                        var date_arr   = data[0][0].order_date.split(' ');
                        var date_split = date_arr[0].split('-');
                        
                        var myDate = new Date(date_split[0], date_split[1] - 1,date_split[2]);
                        var date   = myDate.format('M jS, Y');
                        
                        //Paid Date
                        if (data[0][0].paid_date != '0000-00-00 00:00:00') {
                            var paid_date_arr   = data[0][0].paid_date.split(' ');
                            var paid_date_split = paid_date_arr[0].split('-');
                            
                            var paid_myDate = new Date(paid_date_split[0], paid_date_split[1] - 1, paid_date_split[2]);
                            var paid_date   = paid_myDate.format('M jS, Y');
                        } else {
                            var paid_date   = '';
                        }
                        
                        //Shipped Date
                        if (data[0][0].shipped_date != '0000-00-00 00:00:00') {
                            var shipped_date_arr   = data[0][0].shipped_date.split(' ');
                            var shipped_date_split = shipped_date_arr[0].split('-');
                            
                            var shipped_myDate = new Date(shipped_date_split[0], shipped_date_split[1] - 1, shipped_date_split[2]);
                            var shipped_date   = shipped_myDate.format('M jS, Y');
                        } else {
                            var shipped_date   = '';
                        }
                        
                        var standart = data[0][0].standart == 0 ? '" . _T("No") . "' : '" . _T("Yes") . "';
                    
                        var order_details = '';
                        
                        var total_quanity = 0;
                        
                        //hide shipping adress if we have new adress
                        if (data[0][0].new_adress) {
                            data[0][0].shipping_adress = '';
                        }
                        
                        //Fix null
                        if (data[0][0].new_adress == null) {
                            data[0][0].new_adress = '';
                        }
                        console.log(data[0][0].new_adress);
                        if (data[0][0].other_orders == null) {
                            data[0][0].other_orders = '';
                        }
                        
                        if (data[0][0].more_than_one_adress == null) {
                            data[0][0].more_than_one_adress = '';
                        }
                        
                        $.each( data[1], function( key, value ) {
                            total_quanity += parseInt(value.quantity);
                        });
                        order_details += '\
                        <div class=\"actions_row buttons\">\
                            <span class=\"btn btn-info back_btn\">" . _T("Back") . "</span>\
                            <a href=\"/orders/generate_invoice/' + data[0][0].sale + '\" class=\"btn btn-default generate_btn\" target=\"_blank\">" . _T("Generate Invoice") . "</a>\
                            <a href=\"/orders/generate_order/' + data[0][0].sale + '\" class=\"btn btn-default generate_btn\" target=\"_blank\">" . _T("Generate Order") . "</a>\
                            <a href=\"/orders/generate_orders\" class=\"btn btn-default generate_btn\" target=\"_blank\">" . _T("Generate Orders    ") . "</a>\
                            <a href=\"/orders/history/' + data[0][0].id + '\" class=\" btn btn-default\" target=\"_blank\">" . _T("History") . "</a>\
                            <a href=\"/orders/feedback/' + data[0][0].id + '\" class=\" btn ' + data[0][0].feedback + '\" target=\"_blank\">" . _T("Feedback") . "</a>\
                            <span class=\"pull-right btn-group btn-group-xs\">\
                                <button class=\"btn btn-default zoom_in\" id=\"zoom_in\"><span class=\"glyphicon glyphicon-plus\"></span></button>\
                                <button class=\"btn btn-default zoom_out\" id=\"zoom_out\"><span class=\"glyphicon glyphicon-minus\"></span></button>\
                                <button class=\"btn btn-default zoom_reset\" id=\"zoom_reset\"><span class=\"glyphicon glyphicon-refresh\"></span></button>\
                            </span>\
                        </div>\
                        <table data-id=\"' + data[0][0].order_id + '\" style=\"width:100%; display:none\" class=\"order_single_view\">\
                             <tr class=\"actions_row headers\">\
                                <th></th>\
                                <td>" . _T("Customer information") . "</td>\
                            </tr>\
                            <tr>\
                                <th class=\"set_height\" style=\"font-weight:bold !important;\">" . _T("Set height") . "</th>\
                                <td class=\"set_height\"><br><input step=\"10\" id=\"set_height\"type=\"number\" value=\"' + data[0][0].empty_row_height + '\" style=\"color:#000\"> <span class=\"btn btn-default save_height\" data-id=\"height\" id=\"height_save\">" . _T("Save") . "</span><br><br></td><br>\
                            </tr>\
                            <!--\
                            <tr>\
                                <th class=\"set_emergency\" style=\"color: red; font-weight:bold !important;\">" . _T("Set emergency") . "</th>\
                                <td class=\"set_emergency\" style=\"color: red;\"><input id=\"emergency_checkbox\"type=\"checkbox\" for=\"emergency_notes\"> <textarea style=\"color: #FF6600 !important;opacity: 1;\" id=\"emergency_notes\" name=\"emergency_notes\" disabled> '+ data[0][0].emergency_note +' </textarea><span class=\"btn btn-default save_emergency\" data-id=\"emergency\" id=\"emergency_note_save\">" . _T("Save") . "</span></td><br>\
                            </tr>\
                            -->\
                            <tr>\
                                <th>" . _T("Scheduled to ship") . "</th>\
                                <td>' + scheduled_date + '</td>\
                            </tr>\
                            <tr>\
                                <th>" . _T("Order") . "</th>\
                                <td style=\"line-height:20px;\"><b style=\"font-size:18px;\">' + data[0][0].sale + '</b></td>\
                            </tr>\
                            <tr>\
                                <th>" . _T("With Order") . "</th>\
                                <td>' + data[0][0].other_orders + '</td>\
                            </tr>\
                            <tr>\
                                <th>" . _T("More than one adress") . "</th>\
                                <td>' + data[0][0].more_than_one_adress + '</td>\
                            </tr>\
                            <tr>\
                                <th>" . _T("Date of order") . "</th>\
                                <td>' + date + '</td>\
                            </tr>\
                            <tr>\
                                <th>". _T("Adress") . "</th>\
                                <td>'+ data[0][0].shipping_adress +'</td>\
                            </tr>\
                            <tr>\
                                <th>". _T("Buyer") . "</th>\
                                <td>' + data[0][0].full_name + '</td>\
                            </tr>\
                            <tr>\
                                <th>". _T("Paid Date") . "</th>\
                                <td>' + paid_date + '</td>\
                            </tr>\
                            <tr>\
                                <th>". _T("Shipped Date") . "</th>\
                                <td>' + shipped_date + '</td>\
                            </tr>\
                            <tr>\
                                <th>" . _T("Checked by") . "</th>\
                                <td>\
                                    <select id=\'order_checked\' data-id=\'' + data[0][0].id + '\' class=\'order-checked col-md-4\'>' + data[0][0].order_check_by + '</select>\
                                </td>\
                            </tr>\
                             <tr class=\"actions_row headers\">\
                                <th></th>\
                                <td>" . _T("Notes / Quantity") . "</td>\
                            </tr>\
                            <tr>\
                                <th class=\"yes_no\" style=\"color: red;font-weight:bold !important;\">" . _T("Standard Yes / No") . "</th>\
                                <td class=\"yes_no\" style=\"color: red !important;\">' + standart + '</td>\
                            </tr>\
                            <tr>\
                                <th class=\"note_en\" style=\"color: #FF6600; font-weight:bold !important;\">" . _T("Note buyer") . "</th>\
                                <td class=\"note_en\" style=\"color: #000000; font-weight:bold !important;\">' + comment_checkbox_buyer + ' <textarea rows=\"10\" style=\"color: #000000; opacity: 1;\" id=\"buyer_notes\" name=\"note_en\" disabled> '+ data[0][0].note_from_bayer +' </textarea><span class=\"btn btn-default save_note\" data-id=\"buyer\" id=\"buyer_note_save\">" . _T("Save") . "</span></td><br>\
                            </tr>\
                            <tr>\
                                <th class=\"internal\" style=\"color: #FF6600; font-weight:bold !important;\">" . _T("Note seller") . "</th>\
                                <td class=\"internal\" style=\"color: #000000; font-weight:bold !important;\">' + comment_checkbox_seller + ' <textarea rows=\"10\" style=\"color: #000000; opacity: 1;\" id=\"seller_notes\"name=\"note_seller\" disabled> ' + data[0][0].note_from_seller + '</textarea><span class=\"btn btn-default save_note\" data-id=\"seller\" id=\"seller_note_save\">" . _T("Save") . "</span></td>\
                            </tr>\
                            <tr>\
                                <th class=\"second_adress\" style=\"color: #FF6600; font-weight:bold !important;\">" . _T("Other adress / Phone") . "</th>\
                                <td class=\"second_adress\" style=\" font-weight:bold !important;\">' + new_adress_checkbox + ' <textarea style=\"color: #000000; opacity: 1;\" id=\"newadress_notes\"name=\"new_adress\" disabled> ' + data[0][0].new_adress + '</textarea><span class=\"btn btn-default save_note\" data-id=\"new_adress_data\" id=\"new_adress_save\">" . _T("Save") . "</span></td>\
                            </tr>\
                            <tr>\
                                <th class=\"quantity\" style=\"color: #FF6600;font-weight:bold !important;\">" . _T("Number of products in order") . "</th>\
                                <td class=\"quantity\" style=\"color: #FF6600 !important; font-weight: bold !important; font-size:18px;\">' + total_quanity + '</td>\
                            </tr>\
                            <tr>\
                                <th class=\'text-center\' colspan = \'2\'>" . _T("Comments have priority over everything else!") . "</th>\
                            </tr>\
                             <tr class=\"actions_row headers\">\
                                <th></th>\
                                <td>" . _T("Products information") . "</td>\
                            </tr>\
                        ';

                        var count = data[1].length;
                            
                        $.each( data[1], function( key, value ) {
                            order_details += '\
                                <tr>\
                                    <th>" . _T("Name En") . "</th>\
                                    <td>' + value.name_en + '</td>\
                                </tr>\
                                <tr>\
                                    <th>" . _T("Name Bg") . "</th>\
                                    <td>' + value.name_bg + '</td>\
                                </tr>\
                                <tr>\
                                    <th>" . _T("SKU") . "</th>\
                                    <td>' + value.sku + '</td>\
                                </tr>\
                                <tr>\
                                    <th>" . _T("Size") . "</th>\
                                    <td>' + value.size + '</td>\
                                </tr>\
                                <tr>\
                                    <th>" . _T("Color") . "</th>\
                                    <td id=\"edit-color\">' + value.color + '</td>\
                                </tr>\
                                <tr>\
                                    <th>" . _T("Materials") . "</th>\
                                    <td>' + value.fabric + '</td>\
                                </tr>\
                                <tr>\
                                    <th>" . _T("Quantity") . "</th>\
                                    <td class=\"quantity\" style=\"color: #FF6600 !important; font-weight: bold !important; font-size:18px;\">' + value.quantity + '</td>\
                                </tr>\
                                <tr>\
                                    <th>" . _T("Image") . "</th>\
                                    <td><img src=\"' + value.image_one + '\" style=\"height: 82px;margin-top: 3px;margin-bottom: 3px;border: 1px solid #ddd;\"/></td>\
                                </tr>\
                                <tr>\
                                    <th>" . _T("Image") . "</th>\
                                    <td><img src=\"' + value.image_two + '\" style=\"height: 82px;margin-top: 3px;margin-bottom: 3px;border: 1px solid #ddd;\"/></td>\
                                </tr>\
                                <tr>\
                                    <th>" . _T("Made by") . "</th>\
                                    <td>\
                                        <select data-id=\'' + value.id + '\' data-action=\'made\' class=\'product-made col-md-4\'>' + value.options_made + '</select>\
                                    </td>\
                                </tr>\
                                <tr>\
                                    <th>" . _T("Checked by") . "</th>\
                                    <td>\
                                        <select data-id=\'' + value.id + '\' data-action=\'checked\' class=\'product-checked col-md-4\'>' + value.options_checked + '</select>\
                                    </td>\
                                </tr>\
                                <tr data-id=' + value.id + ' class=\'' + value.color_progress + '\'>\
                                    <th>" . _T("Progress") . "</th>\
                                    <td>\
                                        <select class=\'product-progress col-md-4\'>' + value.options_progress + '</select>\
                                    </td>\
                                </tr>\
                                <tr>\
                                    <th>&nbsp;</th>\
                                    <td>&nbsp;</td>\
                                <tr>\
                            ';
                                
                            if ((count - 1) != key) {
                                order_details += '<tr style=\"background:#ddd\"><th></th><td></td></tr>';
                            }
                            
                        });
                    
                        order_details += '</table>';
                        
                        //previous and next order
                        order_details += '\
                            <br>\
                            <div class=\"btn-group\" role=\"group\" aria-label=\"...\" style=\"clear: both;width: 100%;\">\
                                <button type=\"button\" data-action=\'display\' data-id=\'\' class=\"btn btn-default previous-order\" style=\"float: left;\">" . _T("Previous") . "</button>\
                        <button type=\"button\" data-action=\'display\' data-id=\'\' class=\"btn btn-default next-order\" style=\"float: right;\">" . _T("Next") . "</button>\
                            </div>\
                        ';
                                
                        $('#table-details').html(order_details);
                        
                        //get previous and next order id
                        var current_order_key = arraySearch(order_ids, id);
                        var order_ids_count = order_ids.length;
                        
                        if (current_order_key == 0) {
                            $('.previous-order').attr( 'disabled', 'disabled' );
                        }
                        
                        if (current_order_key == order_ids_count - 1) {
                            $('.next-order').attr( 'disabled', 'disabled' );
                        }
                        
                        $('.previous-order').attr('data-id', order_ids[current_order_key - 1]);
                        $('.next-order').attr('data-id', order_ids[current_order_key + 1]);
                        
                        //show order information
                        $('.order_single_view').show();

                        $('#buyer_note_save').hide();
                        $('#seller_note_save').hide();
                        $('#new_adress_save').hide();
                        $('.product-made, .product-checked, .product-progress, .order-checked').chosen();

                    }, 'json');
                
                } else if (action == 'generate_invoice') {
                    window.open('/orders/generate_invoice/' + id, '_blank');
                } else if (action == 'generate_order') {
                    window.open('/orders/generate_order/' + id, '_blank');
                }

            }
            
            $('#get-time').click(function(event){
                event.preventDefault();
                var fromDateTime   = $('#from_date').val();
                var toDateTime     = $('#to_date').val();
                var order_status   = $('#order-status :selected').val();
                var order_standart = $('#order-standart :selected').val();
                var table = '';
                
                $.post('/orders/fetch_orders_ajax', {
                    from           : fromDateTime,
                    to             : toDateTime,
                    order_status   : order_status,
                    order_standart : order_standart
                }, function(data) {
                
                    $('#table-response').empty();
                    
                    $('#table-response').html('\
                        <table style=\"width:100%;\" class=\"table table-bordered all_orders\">\
                            <thead>\
                                <tr>\
                                    <th>" . _T("Order number") . "</th>\
                                    <th>" . _T("Time limit") . "</th>\
                                    <th>" . _T("Status") . "</th>\
                                    <th>" . _T("Date") . "</th>\
                                    <th>" . _T("Actions") . "</th>\
                                </tr>\
                            </thead>\
                            <tbody>\
                            </tbody>\
                        </table>\
                    ');
                    
                    //clear orders ids
                    order_ids = [];

                    $.each( data, function( key, value ) {
                        //add order id to array
                        order_ids.push(value.id);
                    
                        //Created Date
                        var date_arr   = value.order_date.split(' ');
                        var date_split = date_arr[0].split('-');
                        
                        var myDate = new Date(date_split[0], date_split[1] - 1, date_split[2]);
                        var date   = myDate.format('M jS, Y');
                       
                        //Status
                        if (value.shipped_date == '0000-00-00 00:00:00') {
                            var status = '" . _T("Active") . "';
                        } else {
                            var status = '" . _T("Completed") . "';
                        }

                        //Check if order is checked
                        var checked_by_person = parseInt(value.checked_by_person);

                        if (checked_by_person === 1) {
                            var checked_by_person_class = 'text-success';
                        } else {
                            var checked_by_person_class = null;
                        }

                        table += '\
                            <tr>\
                                <td class=\"order_number\">' + value.sale + '</td>\
                                <td>\
                                  <div class=\"order_table_progress progress\">\
                                    <div class=\"progress-bar\" role=\"progressbar\" aria-valuenow=\"' + value.elapsed_days + '\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width:' + value.elapsed_days + '%; background-color: ' + value.elapsed_days_color + '\">\
                                      <span>' + value.time_limit + '</span>\
                                    </div>\
                                  </div>\
                                </td>\
                                <td><span class=\"' + checked_by_person_class + '\">' + status + '</span></td>\
                                <td>' + date + '</td>\
                                <td><select class=\"show_details\"><option data-action=\"none\" data-id=\"' + value.id + '\" value=\"0\">" . _T("Please choose") . "</option><option value=\"display\" data-action=\"display\" data-id=\"' + value.id + '\">" . _T("Display") . "</option><option data-action=\"generate_invoice\" data-id=\"' + value.sale + '\" value=\"generate_inv\">" . _T("Generate invoice") . "</option><option data-action=\"generate_order\" data-id=\"' + value.sale + '\" value=\"generate_rd\">" . _T("Generate order") . "</option></select></td>\
                            </tr>\
                        ';
                    });
                    
                    $('.all_orders tbody').html(table);
                    $('.all_orders').dynatable();
                    
                }, 'json');

            });
            
            $('#table-response').on('change', 'select.show_details', function() {
                //get order id
                var id      = $(this).find(':selected').data('id');
                var action  = $(this).find(':selected').data('action');
                
                showDetails(id, action);
            });
            
            //set height
            $('#table-details').on('click', '#height_save', function() {
                var height   = $('#set_height').val();
                var order_id = $('#table-details table').data('id');
                
                $.post('/orders/set_row_height_ajax', {
                    height   : height,
                    order_id : order_id
                }, function(data) {
                    modal(data.title, data.body);
                }, 'json');
            });
            
            $('#table-details').on('click', '#buyer_checkbox, #seller_checkbox, #new_adress_checkbox', function() {
                var cb1 = $('#buyer_checkbox').is(':checked');
                var cb2 = $('#seller_checkbox').is(':checked');
                var cb3 = $('#new_adress_checkbox').is(':checked');
                $('#buyer_notes').prop('disabled', !cb1);
                $('#seller_notes').prop('disabled', !cb2);
                $('#newadress_notes').prop('disabled', !cb3);
                
                // TODO: @Stancho to be optimized!!!
                if ($('#buyer_checkbox').is(':checked')) {
                    $('#buyer_note_save').show();
                } else {
                    $('#buyer_note_save').hide();
                }
                
                if ($('#seller_checkbox').is(':checked')) {
                    $('#seller_note_save').show();
                } else {
                    $('#seller_note_save').hide();
                }
                
                if ($('#new_adress_checkbox').is(':checked')) {
                    $('#new_adress_save').show();
                } else {
                    $('#new_adress_save').hide();
                }

            });
            
            $('#table-details').on('click', 'span.back_btn', function() {
                $('.order_single_view').hide();
                $('.actions_row.buttons').hide();
                $('.previous-order, .next-order').hide();
                $('.orders_dates').show();
                $('#table-response').show();
                
                //clear options
                $('.show_details').each(function(index) {
                    $(this).val('0');
                });
                
            });
            
            $('#table-details').on('dblclick', '#edit-color', function() {
                //get color
                var color = $(this).html();
                
                //show input
                $(this).html('<input type=\"text\" id=\"edit-color-action\" class=\"form-control\" name=\"edit-color-action\" value=\"' + color + '\" >');
                
                $('#edit-color-action').keyup(function (e) {
                    if (e.keyCode == 13) {
                        var new_color = $(this).val();
                        $('#edit-color').html(new_color);
                    }
                });
            });
            
            $('#table-details').on('click', 'span#buyer_note_save, span#seller_note_save', function() {
                var order_id = $('#table-details table').data('id');
                var action   = $(this).data('id');
                var comment  = '';
                
                switch (action) {
                    case 'buyer':
                        comment = $('#buyer_notes').val();
                        break;
                    
                    case 'seller':
                        comment = $('#seller_notes').val();
                        break;
                }
                
                $.post('/orders/add_comment_ajax', {
                    order_id : order_id,
                    action   : action,
                    comment  : comment
                }, function(data) {
                    modal(data.title, data.body);
                    switch (action) {
                        case 'buyer':
                            $('#buyer_checkbox').trigger('click');
                            break;
                        
                        case 'seller':
                            $('#seller_checkbox').trigger('click');
                            break;
                    }
                }, 'json');
            });
            
            //save new adress
            $('#table-details').on('click', 'span#new_adress_save', function() {
                var order_id    = $('#table-details table').data('id');
                var new_adress  = $('#newadress_notes').val();
                
                $.post('/orders/add_newadress_ajax', {
                    order_id    : order_id,
                    new_adress  : new_adress
                }, function(data) {
                    modal(data.title, data.body);
                    $('#new_adress_checkbox').trigger('click');
                }, 'json');
            });
            
            $('#table-details').on('click', 'span.print_order_btn', function() {
                modal('Warning', 'In development. Be patient!!!');
            });
            
            //orders pagination
            $('#table-details').on('click', 'button.previous-order, button.next-order', function() {
                //get order id
                var id      = $(this).data('id');
                var action  = $(this).data('action');
                
                showDetails(id, action);
            });

            var isChromium = window.chrome,
            vendorName = window.navigator.vendor;

            if (isChromium !== null && isChromium !== undefined && vendorName === \"Google Inc.\"){
                var currentZoom = 1.0;
                $('#table-details').on('click', 'button.zoom_in', function() {
                    $('#table-details').animate({ 'zoom': currentZoom += .2 }, 'fast');
                });
                
                $('#table-details').on('click', 'button.zoom_out', function() {
                    $('#table-details').animate({ 'zoom': currentZoom -= .2 }, 'fast');
                });

                $('#table-details').on('click', 'button.zoom_reset', function() {
                    currentZoom = 1.0
                    $('#table-details').animate({ 'zoom': 1,  }, 'fast');
                });
            } else {
                var currFFZoom = 1;
                $('#table-details').on('click', 'button.zoom_in', function() {
                    var step = 0.2;
                    currFFZoom += step; 
                    $('#table-details').css('MozTransform','scale(' + currFFZoom + ')');

                });
                $('#table-details').on('click', 'button.zoom_out', function() {
                    var step = 0.2;
                    currFFZoom -= step;                 
                    $('#table-details').css('MozTransform','scale(' + currFFZoom + ')');
                });
                $('#table-details').on('click', 'button.zoom_reset', function() {
                    currentZoom = 1.0
                    $('#table-details').css('MozTransform','scale(1.0)');
                });

            }

            //Change product status
            $('#table-details').on('change', '.product-progress', function() {
                var product_id    = $(this).closest(\"tr\").data('id');
                var selected_val  = $(this).val();
                var order_id      = $('#table-details table').data('id');
                var color = \"\";

                switch(selected_val) {
                    case \"in_progress\":
                        color = 'alert alert-warning';
                        $(this).closest(\"tr\").removeClass().addClass(color);
                        break;
                    case \"ready\":
                        color = 'alert alert-success';
                        $(this).closest(\"tr\").removeClass().addClass(color);
                        break;
                }

                $.post('/orders/change_product_status_ajax', {
                    product_id: product_id,
                    val: selected_val,
                    color: color,
                    order_id: order_id
                }, function(data) {
                    modal(\"" . _T("Successful") . "\", \"" . _T("The status was changed!") . "\");
                });
            });

            $('#table-details').on('change', '.product-made, .product-checked', function() {
                var action      = $(this).data('action');
                var user_id     = $(this).val();
                var product_id  = $(this).data('id');
                var order_id    = $('#table-details table').data('id');

                $.post('set_product_controls_ajax', {
                    action: action,
                    user_id: user_id,
                    product_id: product_id,
                    order_id: order_id
                }, function() {
                    modal(\"" . _T("Successful") . "\", \"" . _T("The assignment was successfully set!") . "\");
                });
            });

            $('#table-details').on('change', '#order_checked', function() {
                var order_id = $(this).data('id');
                var user_id = $(this).val();

                $.post('/orders/order_checked_by_ajax', {
                    order_id : order_id,
                    user_id : user_id
                }, function() {
                    modal(\"" . _T("Successful") . "\", \"" . _T("The order was checked successfully!") . "\");
                });
            });
        ";
        
        $this->view->addContent("jq", $jq);
        
        $this->view->loadPage("orders/display_orders");
    }
    
    /**
     * Fetch orders
     * @author Bobi <me@borislazarov.com> on 10 Dec 2014
     * @return json
     */
    public function fetchOrdersAjax() {
        Authentication::chechAuthentication("exit");
        
        //Track last action
        User::trackLastAction();
        
        $is_valid = \libraries\gump::is_valid($_POST, array(
            'from'           => 'required',
            'to'             => 'required',
            'order_status'   => 'required',
            'order_standart' => 'required'
        ));
         
        if($is_valid === true) {
            $objOrder = new Order();
            $orders = $objOrder->fetchOrders(["from" => $_POST['from'], "to" => $_POST['to'], "order_status" => $_POST['order_status'], "order_standart" => $_POST['order_standart']]);

            foreach ($orders as $key => $value) {
                $elapsed_days = $this->getElapsedDays($value['order_date'], $value['scheduled_to_ship_date']);

                //is checked by person
                $checked = OrderCheck::fetchStatus($value['id'], 'boolean');

                $orders[$key]['elapsed_days_color'] = $elapsed_days['color'];
                $orders[$key]['elapsed_days'] = $elapsed_days['progress'];
                $orders[$key]['time_limit'] = $elapsed_days['time_limit'];
                $orders[$key]['checked_by_person'] = $checked;
            }

            echo json_encode($orders);
        } else {
            print_r($is_valid);
        }
        
    }
    
    /**
     * Fetch order details
     * @author Bobi <me@borislazarov.com> on 10 Dec 2014
     * @return json
     */
    public function fetchOrderAjax() {
        Authentication::chechAuthentication("exit");
        
        //Track last action
        User::trackLastAction();

        //Logger
        Log::logMe("Saw order with id " . $_POST['id']);
        
        $is_valid = \libraries\gump::is_valid($_POST, array(
            'id' => 'required|integer'
        ));
         
        if($is_valid === true) {
            $objOrder    = new Order();
            $order       = $objOrder->fetchOrder($_POST['id']);
            
            //permissions
            $permission = Globals::get("orders_permissions");
            
            if ($permission['display_adress_more']['permission'] == 1) {
                //Add more than one adress
                $moreThanOneOrder = Order::displayAdressMore("not_arranged");
                
                $status = array_search($order[0][0]['sale'], $moreThanOneOrder);
                
                if ($status != false) {
                    $order[0][0]['more_than_one_adress'] = _T("The customer have more than one adress");
                } else {
                    $order[0][0]['more_than_one_adress'] = "";
                }
            } else {
                $order[0][0]['more_than_one_adress'] = _T("You do not have permissions to see this!");
            }

            //Get Feedback
            $objFeedback = new Feedback(['order_id' => $order[0][0]['id']]);
            $feedbackId = $objFeedback->getId();

            if (isset($feedbackId)) {
                if ($objFeedback->getStatus() == 1) {
                    $order[0][0]['feedback'] = "btn-success";
                } elseif ($objFeedback->getStatus() == 2) {
                    $order[0][0]['feedback'] = "btn-warning";
                }
            } else {
                $order[0][0]['feedback'] = "btn-default";
            }

            //Fetch Users
            $users = new User();
            $arrUsers = $users->fetchUsers();

            $orderChecked = OrderCheck::fetchStatus($order[0][0]['id']);

            //set Made
            $setCheckHTML = "<option value=''></option>";

            foreach ($arrUsers as $user) {
                if ($orderChecked === false) {
                    $setCheckHTML .= "<option value='" . $user['id'] . "'>" . _T($user['name']) . "</option>";
                } else {
                    if ($user['id'] === $orderChecked->getUserId()) {
                        $setCheckHTML .= "<option value='" . $user['id'] . "' selected>" . _T($user['name']) . "</option>";
                    } else {
                        $setCheckHTML .= "<option value='" . $user['id'] . "'>" . _T($user['name']) . "</option>";
                    }
                }
            }

            $order[0][0]['order_check_by'] = $setCheckHTML;
            
            //get shipping name
            $tmpName = explode("\n", $order[0][0]["shipping_adress"]);
            $otherOrder  = $objOrder->fetchOtherOrders($tmpName[0]);
            
            //add image and progress
            foreach ($order[1] as $key => $tmpOrder) {
                //fetch progress
                $options = "";
                $color = "";
                $objProductProgress = new ProductProgress(['product_id' => $tmpOrder['id']]);
                $progressId = $objProductProgress->getId();

                if ($progressId === null) {
                    $options .= "
                        <option value='in_progress' selected>" . _T("In progress") . "</option>
                        <option value='ready'>" . _T("Ready") . "</option>
                    ";

                    $color = "alert alert-warning";
                } else {
                    $progressVal = $objProductProgress->getProgress();
                    $color = $objProductProgress->getColor();

                    //in progress
                    if ($progressVal === "in_progress") {
                        $options .= "<option value='in_progress' selected>" . _T("In progress") . "</option>";
                    } else {
                        $options .= "<option value='in_progress'>" . _T("In progress") . "</option>";
                    }

                    //ready
                    if ($progressVal === "ready") {
                        $options .= "<option value='ready' selected>" . _T("Ready") . "</option>";
                    } else {
                        $options .= "<option value='ready'>" . _T("Ready") . "</option>";
                    }
                }

                //Fetch Users
                $users = new User();
                $arrUsers = $users->fetchUsers();

                //Fetch Tasks
                $taskMade = Task::fetchTask($tmpOrder['id'], 'made');
                $taskChecked = Task::fetchTask($tmpOrder['id'], 'checked');

                //set Made
                $setMade = "<option value=''></option>";

                foreach ($arrUsers as $user) {
                    if ($taskMade === false) {
                        $setMade .= "<option value='" . $user['id'] . "'>" . _T($user['name']) . "</option>";
                    } else {
                        if ($user['id'] === $taskMade->getUserId()) {
                            $setMade .= "<option value='" . $user['id'] . "' selected>" . _T($user['name']) . "</option>";
                        } else {
                            $setMade .= "<option value='" . $user['id'] . "'>" . _T($user['name']) . "</option>";
                        }
                    }
                }

                //set Checked
                $setChecked = "<option value=''></option>";

                foreach ($arrUsers as $user) {
                    if ($taskChecked === false) {
                        $setChecked .= "<option value='" . $user['id'] . "'>" . _T($user['name']) . "</option>";
                    } else {
                        if ($user['id'] === $taskChecked->getUserId()) {
                            $setChecked .= "<option value='" . $user['id'] . "' selected>" . _T($user['name']) . "</option>";
                        } else {
                            $setChecked .= "<option value='" . $user['id'] . "'>" . _T($user['name']) . "</option>";
                        }
                    }
                }

                //Tasks
                $order[1][$key]["options_made"] = $setMade;
                $order[1][$key]["options_checked"] = $setChecked;

                //Others
                $order[1][$key]["image_one"] = $this->imgOptimization($tmpOrder["image_one"]);
                $order[1][$key]["image_two"] = $this->imgOptimization($tmpOrder["image_two"]);
                $order[1][$key]["options_progress"] = $options;
                $order[1][$key]["color_progress"] = $color;
            }
            
            //add other orders
            foreach ($otherOrder as $key => $value) {
                //remove duplicate id
                $duplicate_key = array_search($order[0][0]["sale"], $value);
                
                if ($duplicate_key != false) {
                    unset($otherOrder[$key]);
                    $other_orders .= "";
                } else {
                    $dt = new \DateTime($value['order_date']);
                    $other_orders .= $dt->format("d.m.Y") . " " . $value['sale'] . "; ";
                }
    
            }
            $order[0][0]["other_orders"] = $other_orders;
            
            //convert to json
            echo json_encode($order);
        } else {
            //display error
            print_r($is_valid);
        }
    }
    
    /**
     * Add comment to order
     * @author Bobi <me@borislazarov.com> on 15 Dec 2014
     * return string
     */
    public function addCommentAjax() {
        //Track last action
        User::trackLastAction();

        //Logger
        Log::logMe("Added comment");
        
        $gump = new \libraries\gump();
        
        $_POST = $gump->sanitize($_POST); // You don't have to sanitize, but it's safest to do so.
        
        $gump->validation_rules(array(
            'comment'  => 'required|min_len,6',
            'action'   => 'required',
            'order_id' => 'required|integer'
        ));
        
        $gump->filter_rules(array(
            'comment'  => 'trim|sanitize_string',
            'action'   => 'trim',
            'order_id' => 'trim|sanitize_numbers'
        ));
        
        $validated_data = $gump->run($_POST);
        
        if($validated_data === false) {
            Util::modal(true, _T("Error"), $gump->get_readable_errors(true));
        } else {
            $objOrder = new Order($validated_data['order_id']);
            $objOrder->setStandart(0);
            
            switch ($validated_data['action']) {
                case "buyer":
                    $objOrder->setNoteFromBayer($validated_data['comment']);
                    break;
                
                case "seller":
                    $objOrder->setNoteFromSeller($validated_data['comment']);
                    break;
            }
            
            try {
                $objOrder->save();
            } catch (\Exception $e) {
                $this->logger->logEvent($e->getMessage());
            }
            
            Util::modal(true, _T("Succes"), _T("Your comment was added."));
        }
    }
    
    /**
     * Add new adrees
     * @author realdark <me@borislazarov.com> on 4 Jan 2015
     * @return josn
     */
    public function addNewAdressAjax() {
        //Track last action
        User::trackLastAction();

        //Logger
        Log::logMe("Add new address");
        
        $gump = new \libraries\gump();
        
        $_POST = $gump->sanitize($_POST); // You don't have to sanitize, but it's safest to do so.
        
        $gump->validation_rules(array(
            'order_id'   => 'required|integer'
        ));
        
        $gump->filter_rules(array(
            'new_adress' => 'trim|sanitize_string',
            'order_id'   => 'trim|sanitize_numbers'
        ));
        
        $validated_data = $gump->run($_POST);
        
        if($validated_data === false) {
            Util::modal(true, _T("Error"), $gump->get_readable_errors(true));
        } else {
            $objOrder = new Order($validated_data['order_id']);
            $objOrder->setNewAdress($validated_data['new_adress']);
            
            
            try {
                $objOrder->save();
            } catch (\Exception $e) {
                $this->logger->logEvent($e->getMessage());
            }
            
            Util::modal(true, _T("Succes"), _T("The new adress was added."));
        }
    }

    /**
     * Generate order page
     * @author realdark <me@borislazarov.com> on 16 Dec 2014
     * @param $slug
     * @return array
     */
    public function generateOrder($slug) {
        //If user is not logged send him to loggin page
        Authentication::chechAuthentication("exit");
        
        //Track last action
        User::trackLastAction();
        
        //permissions
        $permission = Globals::get("orders_permissions");
        
        if ($permission['generate_single_order']['permission'] == 0) {
            header('Location: /');
        }
        
        $dt = new \DateTime();
        
        $today = getdate();
        
        if ($today["weekday"] == "Monday") {
            $dt->modify('-1 day');
            $yesterday["end"]   = $dt->format('Y-m-d');
            
            $dt->modify('-2 day');
            
            $yesterday["start"] = $dt->format('Y-m-d');
        } else {
            $dt->modify('-1 day');
            
            $yesterday = [
                "start" => $dt->format('Y-m-d'),
                "end"   => $dt->format('Y-m-d')
            ];
        }
        
        //add default date
        $this->view->addContent([
            "start_date" => $yesterday['start'],
            "end_date"  => $yesterday['end'],
        ]);
        
        if ($slug != 0) {
            $saleId = $slug;
        } else {
            $saleId = 0;
        }
        
        //Add information in template
        $this->view->addContent([
            "title"                          => _T("Generate"),
            "Generate Invoice"               => _T("Generate Invoice"),
            "Generate order"                 => _T("Generate order"),
            "Generade orders list"           => _T("Generade orders list"),
            "Close"                          => _T("Close"),
            "Warning!"                       => _T("Warning!"),
            "There is no such order number." => _T("There is no such order number."),
            "Enter the order number"         => _T("Enter the order number"),
            "Generate order"                 => _T("Generate order"),
            "Generate single orders list"    => _T("Generate single order list"),
            "Generate single order"          => _T("Generate single order"),
            "Single order"                   => _T("Single order"),
            "Generate"                       => _T("Generate"),
            "Orders"                         => _T("Orders"),
            "from date"                      => _T("from date"),
            "to date"                        => _T("to date"),
            "Select type"                    => _T("Select type"),
            "Select status"                  => _T("Select status"),
            "Status"                         => _T("Status"),
            "Active"                         => _T("Active"),
            "Completed"                      => _T("Completed"),
            "All"                            => _T("All"),
            "Standart"                       => _T("Standart"),
            "Yes"                            => _T("Yes"),
            "No"                             => _T("No"),
            "Generate"                       => _T("Generate"),
            "Generate orders list"           => _T("Generate orders list"),
            "Orders list"                    => _T("Orders list"),
            "Select type"                    => _T("Select type"),
            "Select status"                  => _T("Select status"),
            "Orders"                         => _T("Orders")
        ]);
        
        //jQuery
        $jq = "
            //hide second options for printing
            //$('#multipleSingleOrdersClose .titleToggle span').trigger('click');
            $('#singleOrderClose .titleToggle span').trigger('click');
        
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
        
            //hide aler msg
            $('#show-id-error').hide();
            
            //sale id
            var sale_id = " . $saleId . ";
            
            //show and hide close btn
            if (sale_id != 0) {
                $('#order_number').val(sale_id);
                $('#close').show();
            } else {
                $('#close').hide();
            }
            
            //close window
            $('#close').click(function() {
                window.close();
            });
            
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
        
            $('#generate').click(function() {
                var order_id = $('#order_number').val();
                load.show();
            
                $.post('/orders/generate_order_ajax', {order_number : order_id}, function(data) {
                    load.hide();
                    file.download(data.file, 'order_' + order_id + '.pdf');
                }, 'json');
            });
            
            //Generate single orders
            $('#generate_single').click(function(event) {
                event.preventDefault();
            
                var from           = $('#from_date_single').val();
                var to             = $('#to_date_single').val();
                var order_status   = $('#order-status_single :selected').val();
                var order_standart = $('#order-standart_single :selected').val();
                
                load.show();
                
                $.post('/orders/generate_orders_ajax', {
                    from           : from,
                    to             : to,
                    order_status   : order_status,
                    order_standart : order_standart,
                    type           : 'single'
                }, function(data) {
                    load.hide();
                    file.download(data.link, 'orders_listing_' + from.replace(/\-/g, '') + '_to_' + to.replace(/\-/g, '') + '.pdf');
                }, 'json');
            });
        ";
        
        $this->view->addContent("jq", $jq);
        
        //Render Template
        $this->view->loadPage("orders/generate_order");
    }
    
    /**
     * Order PDF Template
     * @author SBYDev <s.b.jordanov@gmail.com> on 16 Dec 2014
     * @return strong
     */
    private function orderTemplate($values = [], $newPage = false) {
        $standart   = $values['standart'] == 0 ? 'red' : 'yellow';
        $std_yes_no = $values['standart'] == 0 ? _T("No") : _T("Yes");
        
        if (!empty($values['new_adress'])) {
            $values['shipping_adress'] = "";
        }

        $string = "
         <style>
             h5 {
                font-size: 1.3em;
             }
            table {
                border-spacing: 0px;
                border-collapse: collapse;
                width: 100%;
                font-size: 14px;
                line-height: 22px;
            }
            td,tr,table,th {
                border: 1px solid black;
            }

            td {
                height: 28px;
                padding: 5px;
            }
            .footer table {
                border:none;
            }
            .footer td {
                border:none;
            }
            .footer tr {
                border: none;
            }
            .italic {
                font-style: italic;
            }
            table_footer {
                border: 1px solid #fff;
            }
            .yellow{
                background-color: rgb(255,255,0) !important;
                padding:400px;
            }
            .red{
                color: rgb(255,0,0) !important;
                padding:400px;
            }
            .red.bold {
                color: rgb(255,0,0) !important;
                font-weight:bold;
            }
            .red.quantity {
                color: rgb(255,0,0) !important;
                font-size:20px;
            }
        </style>
        <h5 style=\"text-align:center\">" . _T("For the order") . "</h5>
        <table class=\"table table-bordered single_order first\" cellpadding=\"1px\">
            <thead>
                <tr>
                    <th>" . _T("Date of purchese") . "</th>
                    <td>{$values['order_date']}</td>
                    <th>" . _T("Adress") . "</th>
                    <td>" . nl2br($values['shipping_adress']) . "</td>
                </tr>
                <tr>
                    <th>" . _T("Order") . "</th>
                    <td style=\"line-height:20px;\"><b style=\"font-size:18px;\">{$values['sale']}</b></td>
                    <th>" . _T("Buyer") . "</th>
                    <td>{$values['full_name']}</td>
                </tr>
                <tr>
                    <th>" . _T("Scheduled to ship") . "</th>
                    <td>{$values['scheduled_to_ship_date']}</td>
                    <th style=\"line-height:20px;\">" . _T("Number of products in order") . "</th>
                    <td style=\"line-height:20px;\"><b style=\"font-size:18px; text-align:center;\" class=\"yellow\">{$values['quantity']}</b></td>
                </tr>
                <tr>
                    <td>" . _T("With order/s") . "</td>
                    <td colspan=\"3\"><span class=\"internal\" style=\"background-color: rgb(255,255,0) !important\">" . $values['other_orders'] . "</span></td>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>

        <h5 style=\"text-align:center\">" . _T("Notes") . "</h5>
        <table class=\"table table-bordered single_order second\" cellpadding=\"1px\">
            <thead>
                <tr>
                    <th style=\"width:25%;\">" . _T("Standard Yes / No") . "</th>
                    <td style=\"width:75%;\"><span class=\"yes_no {$standart}\">{$std_yes_no}</span></td>
                </tr>
                <tr>
                    <th style=\"width:25%;\">" . _T("Note buyer") . "</th>
                    <td style=\"width:75%;\"><span class=\"note_en\">" . nl2br($values['note_from_bayer']) . "</span></td><br>
                </tr>
                <tr>
                    <th style=\"width:25%;\">" . _T("Note seller") . "</th>
                    <td style=\"width:75%;\"><span class=\"internal\" style=\"background-color: rgb(255,255,0) !important\">" . nl2br($values['note_from_seller']) . "</span></td>
                </tr>
                <tr>
                    <th style=\"width:25%;\">" . _T("New adress/phone") . "</th>
                    <td style=\"width:75%;\"><span class=\"internal\" style=\"background-color: rgb(255,255,0) !important\">" . nl2br($values['new_adress']) . "</span></td>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>

        <h5 style=\"text-align:center\">" . _T("Products information") . "</h5>
        <table class=\"table table-bordered single_order third\" style=\"border:none\">
            <thead>
                <tr>
                    <th style=\"width:23%; border:1px solid #000000;\">" . _T("Name En") . "</th>
                    <th style=\"width:23%; border:1px solid #000000;\">" . _T("Name Bg") . "</th>
                    <th style=\"width:10%; border:1px solid #000000;\">" . _T("Номер") . "</th>
                    <th style=\"width:8%; border:1px solid #000000;\">" . _T("Size") . "</th>
                    <th style=\"width:8%; border:1px solid #000000;\">" . _T("Color") . "</th>
                    <th style=\"width:10%; border:1px solid #000000;\">" . _T("Материя") . "</th>
                    <th style=\"width:6%; border:1px solid #000000;\">" . _T("Кол") . "</th>
                    <th style=\"width:12%; border:1px solid #000000;\">" . _T("Image") . "</th>
                </tr>
            </thead>
            <tbody>";
                
            foreach ($values['shopping_cart'] as $cart) {
                $quantity = $cart['quantity'] == 1 ? 'yellow' : 'red quantity';

                $string .= "
                    <tr nobr=\"true\">
                        <td style=\"width:23%; border:none; font-size:11px; !important\"><br/>{$cart['name_en']}</td>
                        <td style=\"width:23%; border:none; font-size:11px; !important\"><br/>{$cart['name_bg']}</td>
                        <td style=\"width:10%; border:none;\"><br/>{$cart['sku']}</td>
                        <td style=\"width:8%; border:none;\"><br/><br/><br/><span class=\"yellow\" style=\"vertical-align:middle; text-align:center;\">{$cart['size']}</span></td>
                        <td style=\"width:8%; border:none;\"><br/><br/><br/><span class=\"yellow\" style=\"vertical-align:middle; text-align:center;\">{$cart['color']}</span></td>
                        <td style=\"width:10%; border:none;\"><br/>{$cart['fabric']}</td>
                        <td style=\"width:6%; border:none;\"><br/><br/><br/><span class=\"quantity {$quantity}\" style=\"vertical-align:middle; text-align:center;\">{$cart['quantity']}</span></td>
                        <td style=\"width:12%; border:none;\"><img src=\"http://appdev.aakasha.com" . $this->imgOptimization($cart['image']) . "\" style=\"height:120px !important; width:80px !imporant\"/></td>
                    </tr>
                    <hr style=\"margin-top:5px; margin-bottom:5px; padding:0 !important\">
                ";
            }

            $string .= "</tbody>
            </table>

            <h5 style=\"text-align:center\">" . _T("Control") . "</h5>

            <table nobr=\"true\" style=\"border:none\">
                <tr>
                    <table style=\"border:none\">
                        <tr style=\"border:none\">
                            <th style=\"border:none\">". _T("Folded by") ."</th>
                        </tr>
                    </table>
                </tr>
                <hr>
                <tr>
                    <table class=\"table table-bordered single_order fourth\" style=\"border:none !important;\">
                            <hr>
                            <tr style=\"border:none\">
                                <th style=\"border:none\">". _T("Bust") ."</th>
                                <th style=\"border:none\"></th>
                                <th style=\"width:8%; border:none\">". _T("Waist") ."</th>
                                <th style=\"border:none\"></th>
                                <th style=\"border:none\">". _T("Hip") ."</th>
                                <th style=\"border:none\"></th>
                                <th style=\"border:none\">". _T("Biceps") ."</th>
                                <th style=\"border:none\"></th>
                                <th style=\"width:12%; border:none\">". _T("Height") ."</th>
                                <th style=\"border:none\"></th>
                            </tr>
                            <hr>
                            <tr style=\"border:none\">
                                <th style=\"border:none\">". _T("Lenght") ."</th>
                                <th style=\"border:none\"></th>
                                <th style=\"width:8%; border:none\">". _T("Inseam") ."</th>
                                <th style=\"border:none\"></th>
                                <th style=\"border:none\">". _T("Lenght sleev") ."</th>
                                <th style=\"border:none\"></th>
                                <th style=\"border:none\">". _T("Others") ."</th>
                                <th style=\"border:none\"></th>
                                <th style=\"width:12%; border:none\">". _T("Shoulder to shoulder") ."</th>
                                <th style=\"border:none\"></th>
                            </tr>
                            <hr>
                    </table>
                </tr>
                <tr>
                    <table style=\"border:none\">
                            <tr style=\"border:none\">
                                <th style=\"border:none; color: #fff\">". _T("Packed by") ."</th>
                            </tr>
                            <hr>
                            <tr style=\"border:none\">
                                <th style=\"border:none\">". _T("Packed by") ."</th>
                            </tr>
                            <hr>
                            <tr style=\"border:none\">
                                <th style=\"border:none\">". _T("Checked by") ."</th>
                            </tr>
                            <hr>
                            <tr style=\"border:none\">
                                <th style=\"border:none\">". _T("Quantity") ."</th>
                            </tr>
                            <hr>
                            <tr style=\"border:none\">
                                <th style=\"border:none\">". _T("Specific by model") ."</th>
                            </tr>
                            <hr>
                    </table>
                </tr>
            </table>

        ";
        
        if ($newPage == true) {
            $string .= "<br pagebreak=\"true\"/>";
        }
        
        return $string;
    }
    
    /**
     * Generate Order
     * @author realdark <me@borislazarov.com> on 16 Dec 2014
     * @return string
     */
    public function generateOrderAjax() {
        Authentication::chechAuthentication("exit");
        
        //Track last action
        User::trackLastAction();
        
        //include tcpdf librarie
        include_once(APP_PATH . "libraries/tcpdf/tcpdf.php");
        
        // create tcpdf
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // order number
        $orderNumber = Request::get("order_number", "integer");

        //Logger
        Log::logMe("Generated order " . $orderNumber);

        // set document information
        $pdf->SetAuthor('Aakasha Ltd');
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(-100);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE);
        
        // set font
        $pdf->SetFont('helvetica', 'B', 12);
        
        // add a page
        $pdf->AddPage();
        
        //$pdf->Write(0, 'Order', '', 0, 'L', true, 0, false, false, 0);
        
        $pdf->setFontSubsetting(true);
        $pdf->SetFont('freeserif', '', 12);
        
        //Place here order details
        $objOrder  = new Order();
        $order_tmp = $objOrder->fetchOrder($orderNumber, "more information");
        
        //permissions
        $permission = Globals::get("orders_permissions");
        
        if ($permission['display_adress_more']['permission'] == 1) {
            //Add more than one adress
            $moreThanOneOrder = Order::displayAdressMore("not_arranged");
            
            $status = array_search($order_tmp[0][0]['sale'], $moreThanOneOrder);
            
            if ($status != false) {
                $order_tmp[0][0]['more_than_one_adress'] = _T("The customer have more than one adress");
            } else {
                $order_tmp[0][0]['more_than_one_adress'] = "";
            }
        } else {
            $order_tmp[0][0]['more_than_one_adress'] = _T("You do not have permissions to see this!");
        }
        
        $shipping_adress = explode("\n", $order_tmp[0][0]['shipping_adress']);
        
        $objOrder    = new Order();
        $otherOrders = $objOrder->fetchOtherOrders($shipping_adress[0]);
        
        $other_orders = null;
        //add other orders
        foreach ($otherOrders as $key => $var) {
            //remove duplicate id
            $duplicate_key = array_search($order_tmp[0][0]['sale'], $var);
            
            if ($duplicate_key != false) {
                unset($otherOrder[$key]);
                $other_orders .= "";
            } else {
                $dt = new \DateTime($var['order_date']);
                $other_orders .= $dt->format("d.m.Y") . " " . $var['sale'] . "; ";
            }
        
        }
        
        //order
        $date                            = new \DateTime($order_tmp[0][0]['scheduled_to_ship_date']); //scheduled to ship date
        $order['sale']                   = $order_tmp[0][0]['sale'];
        $order['scheduled_to_ship_date'] = $date->format('d.m.Y');
        $order['standart']               = $order_tmp[0][0]['standart'];
        $order['note_from_bayer']        = $order_tmp[0][0]['note_from_bayer'];
        $order['note_from_seller']       = $order_tmp[0][0]['note_from_seller'];
        $date                            = new \DateTime($order_tmp[0][0]['order_date']); //order date
        $order['order_date']             = $date->format('d.m.Y');
        $order['full_name']              = $order_tmp[0][0]['full_name'];
        $order['shipping_adress']        = $order_tmp[0][0]['shipping_adress'];
        $order['more_than_one_adress']   = $order_tmp[0][0]['more_than_one_adress'];
        $order['new_adress']             = $order_tmp[0][0]['new_adress'];
        
        //other other
        $order['other_orders'] = $other_orders;
        
        //shopping cvart
        foreach ($order_tmp[1] as $key => $value) {
            $order['shopping_cart'][$key]['sale']     = $value['order_id'];
            $order['shopping_cart'][$key]['name_en']  = $value['name_en'];
            $order['shopping_cart'][$key]['name_bg']  = $value['name_bg'];
            $order['shopping_cart'][$key]['quantity'] = $value['quantity'];
            $order['shopping_cart'][$key]['fabric']   = $value['fabric'];
            $order['shopping_cart'][$key]['color']    = $value['color'];
            $order['shopping_cart'][$key]['size']     = $value['size'];
            $order['shopping_cart'][$key]['sku']      = $value['sku'];
            $order['shopping_cart'][$key]['image']    = $value['image_one'];
            
            //qnt
            $order['quantity'] += $value['quantity'];
        }
        
        //generate table
        $tbl = $this->orderTemplate($order);
        
        //write table
        $pdf->writeHTML($tbl, true, false, true, false, '');
        
        $uniqId = uniqid();
        $file   = ROOT_PATH . 'uploads/orders/' . $uniqId . ".pdf";
        
        try {
            //save to file
            $pdf->Output($file, 'F');
        } catch (\Exception $e) {
            $this->logger->logEvent($e->getMessage());
        }
        
        $data['file'] = DIR . 'uploads/orders/' . $uniqId . ".pdf";
        echo json_encode($data);
    }
    
    /**
     * Generate orders page
     * @author realdark <me@borislazarov.com> on 18 Dec 2014
     * @return string
     */
    public function generateOrders() {
        Authentication::chechAuthentication("exit");
        
        //Track last action
        User::trackLastAction();
        
        //permissions
        $permission = Globals::get("orders_permissions");
        
        if ($permission['generate_orders_list']['permission'] == 0) {
            header('Location: /');
        }
        
        $dt = new \DateTime();
        
        $today = getdate();
        
        if ($today["weekday"] == "Monday") {
            $dt->modify('-1 day');
            $yesterday["end"]   = $dt->format('Y-m-d');
            
            $dt->modify('-2 day');
            
            $yesterday["start"] = $dt->format('Y-m-d');
        } else {
            $dt->modify('-1 day');
            
            $yesterday = [
                "start" => $dt->format('Y-m-d'),
                "end"   => $dt->format('Y-m-d')
            ];
        }
        
        //add default date
        $this->view->addContent([
            "start_date" => $yesterday['start'],
            "end_date"  => $yesterday['end'],
        ]);
        
        $this->view->addContent([
            "title"                => _T("Generate orders"),
            "Generate Invoice"     => _T("Generate Invoice"),
            "Generate order"       => _T("Generate order"),
            "Generade orders list" => _T("Generade orders list"),
            "Generate orders"      => _T("Generate orders"),
            "from date"            => _T("from date"),
            "to date"              => _T("to date"),
            "Status"               => _T("Status"),
            "Active"               => _T("Active"),
            "Completed"            => _T("Completed"),
            "All"                  => _T("All"),
            "Standart"             => _T("Standart"),
            "Cut"                  => _T("Cut"),
            "Yes"                  => _T("Yes"),
            "No"                   => _T("No"),
            "Generate"             => _T("Generate"),
            "Generate orders list" => _T("Generate orders list"),
            "Orders list"          => _T("Orders list"),
            "Select type"          => _T("Select type"),
            "Select status"        => _T("Select status"),
            "Orders"               => _T("Orders"),
            "Close"                => _T("Close"),
        ]);
        
        $jq = "
            $(function() {
              $('#from_date').datepicker({
                format: 'yyyy-mm-dd'
              });
            });
            
            $(function() {
              $('#to_date').datepicker({
                format: 'yyyy-mm-dd'
              });
            });

            //show and hide close btn
            var refer_page = document.referrer;
            
            if (refer_page.indexOf('/orders/display_orders') != -1) {
                $('#close').show();
            } else {
                $('#close').hide();
            }
            
            //close window
            $('#close').click(function() {
                window.close();
            });
            
            //Generate multiple orders
            $('#generate').click(function(event) {
                event.preventDefault();
            
                var from           = $('#from_date').val();
                var to             = $('#to_date').val();
                var order_status   = $('#order-status :selected').val();
                var order_standart = $('#order-standart :selected').val();
                
                load.show();
                
                $.post('/orders/generate_orders_ajax', {
                    from           : from,
                    to             : to,
                    order_status   : order_status,
                    order_standart : order_standart,
                    type           : 'multiple'
                }, function(data) {
                    load.hide();
                    file.download(data.link, 'orders_' + from.replace(/\-/g, '') + '_to_' + to.replace(/\-/g, '') + '.pdf');
                }, 'json');
            });
        ";
        
        $this->view->addContent("jq", $jq);
        
        $this->view->loadPage("orders/generate_orders");
    }
    
    /**
     * Generate orders pdf file
     * @author realdark <me@borislazarov.com> on 18 Dec 2014
     * @return json
     */
    public function generateOrdersAjax() {
        Authentication::chechAuthentication("exit");
        
        //Track last action
        User::trackLastAction();

        //Logger
        Log::logMe("Generated orders");
        
        $is_valid = \libraries\gump::is_valid($_POST, array(
            'from'           => 'required',
            'to'             => 'required',
            'order_status'   => 'required',
            'order_standart' => 'required'
        ));
         
        if($is_valid === true) {
            $objOrder = new Order();
            $orders = $objOrder->fetchOrders(["from" => $_POST['from'], "to" => $_POST['to'], "order_status" => $_POST['order_status'], 'order_standart' => $_POST['order_standart']], "extended");
            
            //generate pdf and return link to file
            switch ($_POST['type']) {
                case "multiple":
                    $link = $this->generateOrdersPdf($orders);
                    break;
                case "single":
                    $link = $this->generateSingleOrdersPdf($orders);
            }
            
            //echo json link
            echo json_encode(['link' => $link]);
        } else {
            print_r($is_valid);
        }
    }
    
    /**
     * Orders html template
     * @author stancho <s.b.jordanov@gmail.com> on 18 Dec 2014
     * @return string
     */
    private function ordersTemplate($values = []) {
        $standart   = $values['standart'] == 0 ? 'red' : 'yellow';
        $std_yes_no = $values['standart'] == 0 ? _T("No") : _T("Yes");

        $withOrder = empty($values['with_order']) ? NULL : _T("With order/s") . "<b style=\"font-size:12px;\"> {$values['with_order']}</b>";
        
        $string = "
         <style>
            * {
               zoom: 200% !important;
            }
             h5 {
                font-size: 1.0em;
             }
            table {
                zoom: 200%
                border-spacing: 0px;
                border-collapse: collapse;
                width: 100%;
                font-size: 11px;
                line-height: 14px;
            }

            .italic {
                font-style: italic;
            }
            .yellow{
                background-color: rgb(255,255,0) !important;
            }
            .red{
                background-color: rgb(255,0,0) !important;
            }
            .red.bold {
                text-decoration: underline;
                font-weight:bold;
                background-color: rgb(255,255,255) !important;
            }
            .green {
                color: rgb(255,0,0) !important;
                background-color: rgb(255,0,0) !important;
            }
            .internal {
                font-size: 12px;
                line-height: 14px;
                text-alight: bottom;
            }
            .new {
                text-decoration: underline;
                font-weight:bold;
            }
        </style>
        <div style=\"line-height:" . $values['empty_row_height'] . "px;\"> </div>
    <table align=\"left\" style=\"zoom: 200% !important\">
        <thead> <tr><td></td></tr></thead>
        <tbody>
    
        <tr nobr=\"true\">
            <td>
                <table>
                    <tr>
                        <td>
                            <table border=\"1\">
                                <tr>
                                    <td style=\"width:50%; line-height:18px; !important;\"><b style=\"font-size:14px; text-align:left\">{$values['sale']}</b> - <b>{$values['buyer']}</b></td>
                                    <td style=\"width:50%; line-height:18px; border-right:none !important;\">" . $withOrder . "</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table border=\"1\">
                                <tr>
                                    <td>" . _T("Date of purchese") . "</td>
                                    <td>" . _T("Scheduled to ship") . "</td>
                                    <td>" . _T("Number of products in order") . "</td>
                                    <td>" . _T("Standard Yes / No") . "</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table border=\"1\" cellpadding=\"1px\">
                                <tr>
                                    <td style=\"text-align:center; line-height:18px; vertical-align: middle;\">{$values['order_date']}</td>
                                    <td style=\"text-align:center; line-height:18px; vertical-align: middle;\">{$values['scheduled_to_ship_date']}</td>
                                    <td style=\"text-align:center;\"><b style=\"font-size:14px;\">{$values['quantity']}</b></td>
                                    <td style=\"text-align:center; line-height:18px; vertical-align: middle;\"><span class=\"yes_no {$standart}\">{$std_yes_no}</span></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table border=\"1\" cellpadding=\"1px\">
                                <tr>
                                    <th style=\"width:25%;\">" . _T("Note buyer") . "</th>
                                    <td style=\"width:75%;\"><span class=\"note_en yellow\">" . nl2br($values['note_from_bayer']) . "</span></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table border=\"1\" cellpadding=\"1px\">
                                <tr>
                                    <th style=\"width:25%;\">" . _T("Note seller") . "</th>
                                    <td style=\"width:75%;\" align=\"bottom\"><span class=\"internal yellow\">" . nl2br($values['note_from_seller']) . "</span></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">
                                <tr>
                                    <th style=\"width:25%;\">" . _T("Name Bg") . "</th>
                                    <th style=\"width:10%;\">" . _T("Номер") . "</th>
                                    <th style=\"width:10%;\">" . _T("Size") . "</th>
                                    <th style=\"width:10%;\">" . _T("Color") . "</th>
                                    <th style=\"width:10%;\">" . _T("Материя") . "</th>
                                    <th style=\"width:8%;\">" . _T("Кол") . "</th>
                                    <th style=\"width:12%;\">" . _T("Кройка") . "</th>
                                    <th style=\"width:15%;\">" . _T("Image") . "</th>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
                                foreach ($values['shopping_cart'] as $cart) {

                                    $quantity = $cart['quantity'] == 1 ? '' : 'new';
                                    $standart   = $values['standart'] == 0 ? 'red' : 'yellow';
                                    $string .= "
                                        <tr style=\"margin:0; padding: 0;\">
                                            <td style=\"width:25%; text-align:left;\" class=\"noborder\">
                                               <span style=\"text-align:left; font-size: 14px;\">{$cart['name_bg']}</span>
                                            </td>
                                            <td style=\"width:10%; text-align:left;\" class=\"noborder\">
                                                <span style=\"text-align:left; font-weight: bold;\">{$cart['sku']}</span>
                                            </td>
                                            <td style=\"width:10%;\" class=\"noborder\">
                                                    <span style=\"text-align:left;\" class=\" yellow\">{$cart['size']}</span>
                                            </td>
                                            <td style=\"width:10%;\" class=\"noborder\">
                                                    <span style=\"text-align:left;\" class=\" yellow\">{$cart['color']}</span>
                                            </td>
                                            <td style=\"width:10%; text-align:left;\" class=\"noborder\">
                                                <span style=\"text-align:left;\">{$cart['fabric']}</span>
                                            </td>
                                            <td style=\"width:8%; text-align:left;\" class=\"noborder\">
                                                    <span style=\"text-align:left;\" class=\"quantity {$quantity}\" style=\"font-size:18px;\">{$cart['quantity']}</span>
                                            </td>
                                            <td style=\"width:12%; text-align:left;\" class=\"noborder\">
                                                    <!--<span style=\"text-align:left;\" class=\"yes_no {$standart}\"></span>-->
                                                    <span style=\"text-align:left;\">{$cart['cut_no']}</span>
                                            </td>
                                            <td style=\"width:15%; text-align:left;\" class=\"noborder\">
                                                    <img style=\"width:40px; height:80px;\"src=\"http://appdev.aakasha.com" . $this->imgOptimization($cart['image']) . "\" />
                                            </td>
                                        </tr><hr/ style=\"margin:0; padding: 0;\">";
                                }   
                            $string .= "
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tbody>
        <tfoot><tr><td style=\" background-color: rgb(66,139,202) !important; text-align:center\"></td></tr></tfoot>
    </table>";
        return $string;
    }
    
    /**
     * Generate pdf
     * @author realdark <me@borislazarov.com> on 18 Dec 2014
     * @return string
     */
    public function generateOrdersPdf($value) {
        Authentication::chechAuthentication("exit");
        
        //Track last action
        User::trackLastAction();

        //Logger
        Log::logMe("Generated orders");
        
        //include tcpdf librarie
        include_once(APP_PATH . "libraries/tcpdf/tcpdf.php");
        
        // create tcpdf
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // order number
        $orderNumber = Request::get("order_number", "integer");
        
        // set document information
        $pdf->SetAuthor('Aakasha Ltd');
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(-100);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE);
        
        // set font
        $pdf->SetFont('freeserif', 'B', 14);
        
        // add a page
        $pdf->AddPage('L');
        
        //$pdf->Write(0, 'Order', '', 0, 'L', true, 0, false, false, 0);
        
        $pdf->setFontSubsetting(true);
        $pdf->SetFont('freeserif', '', 12);
        
        foreach ($value['orders'] as $key => $order_tmp) {
            $order = [];
            
            $shipping_adress = explode("\n", $order_tmp['shipping_adress']);
            
            $objOrder    = new Order();
            $otherOrders = $objOrder->fetchOtherOrders($shipping_adress[0]);
            
            $other_orders = null;
            
            //add other orders
            foreach ($otherOrders as $keyTwo => $var) {
                //remove duplicate id
                $duplicate_key = array_search($order_tmp['sale'], $var);
                
                if ($duplicate_key != false) {
                    unset($otherOrder[$keyTwo]);
                    $other_orders .= "";
                } else {
                    $dt = new \DateTime($var['order_date']);
                    $other_orders .= $dt->format("d.m.Y") . " " . $var['sale'] . "; ";
                }
            
            }
            
            //permissions
            $permission = Globals::get("orders_permissions");
            
            if ($permission['display_adress_more']['permission'] == 1) {
                //Add more than one adress
                $moreThanOneOrder = Order::displayAdressMore("not_arranged");
                
                $status = array_search($order_tmp['sale'], $moreThanOneOrder);
                
                if ($status != false) {
                    $order_tmp['more_than_one_adress'] = _T("The customer have more than one adress");
                } else {
                    $order_tmp['more_than_one_adress'] = "";
                }
            } else {
                $order_tmp['more_than_one_adress'] = _T("You do not have permissions to see this!");
            }
            
            //order
            $order['sale']                   = $order_tmp['sale'];
            $order['with_order']             = $other_orders;
            $date                            = new \DateTime($order_tmp['scheduled_to_ship_date']); //scheduled to ship date
            $order['scheduled_to_ship_date'] = $date->format('d.m.Y');
            $order['standart']               = $order_tmp['standart'];
            $order['note_from_bayer']        = $order_tmp['note_from_bayer'];
            $order['note_from_seller']       = $order_tmp['note_from_seller'];
            $date                            = new \DateTime($order_tmp['order_date']); //order date
            $order['order_date']             = $date->format('d.m.Y');
            $order['full_name']              = $order_tmp['full_name'];
            $order['country']                = end($shipping_adress);
            $order['more_than_one_adress']   = $order_tmp['more_than_one_adress'];
            $order['empty_row_height']       = $order_tmp['empty_row_height'];
            
            //fetch buyer
            $order['buyer']  = $shipping_adress[0];
            
            //shopping cart
            $i = 0;
            foreach ($value['cart'][$order_tmp['sale']] as $key_one => $values) {
                $order['shopping_cart'][$i]['name_bg']  = $values['name_bg'];
                $order['shopping_cart'][$i]['quantity'] = $values['quantity'];
                $order['shopping_cart'][$i]['fabric']   = $values['fabric'];
                $order['shopping_cart'][$i]['color']    = $values['color'];
                $order['shopping_cart'][$i]['size']     = $values['size'];
                $order['shopping_cart'][$i]['sku']      = $values['sku'];
                $order['shopping_cart'][$i]['image']    = $values['image_one'];
                
                $objProducts = new Products(['sku' => $values['sku']], ['cut_no']);
                
                //cut no
                $order['shopping_cart'][$i]['cut_no'] = $objProducts->getCutNo();
                
                //qnt
                $order['quantity'] += $values['quantity'];
                
                $i++;
            }
            
            //generate table
            $tbl = $this->ordersTemplate($order);
            
            //write table
            $pdf->writeHTML($tbl, true, false, true, true, '');
            //$pdf->addPage();
        }

        $uniqId = uniqid();
        $file   = ROOT_PATH . 'uploads/orders/' . $uniqId . ".pdf";
        
        try {
            //save to file
            $pdf->Output($file, 'F');
        } catch (\Exception $e) {
            $this->logger->logEvent($e->getMessage());
        }
        
        $link = DIR . 'uploads/orders/' . $uniqId . ".pdf";
        
        return $link;
    }
    
    /**
     * Generate pdf
     * @author realdark <me@borislazarov.com> on 18 Dec 2014
     * @return string
     */
    public function generateSingleOrdersPdf($value) {
        Authentication::chechAuthentication("exit");
        
        //Track last action
        User::trackLastAction();

        //Logger
        Log::logMe("Generated list from single order");
        
        //include tcpdf librarie
        include_once(APP_PATH . "libraries/tcpdf/tcpdf.php");
        
        // create tcpdf
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // order number
        $orderNumber = Request::get("order_number", "integer");
        
        // set document information
        $pdf->SetAuthor('Aakasha Ltd');
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(-100);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE);
        
        // set font
        $pdf->SetFont('helvetica', 'B', 20);
        
        // add a page
        $pdf->AddPage();
        
        //$pdf->Write(0, 'Order', '', 0, 'L', true, 0, false, false, 0);
        
        $pdf->setFontSubsetting(true);
        $pdf->SetFont('freeserif', '', 12);
        
        foreach ($value['orders'] as $key => $order_tmp) {
            $order = [];
            
            $shipping_adress = explode("\n", $order_tmp['shipping_adress']);
            
            $objOrder    = new Order();
            $otherOrders = $objOrder->fetchOtherOrders($shipping_adress[0]);
            
            $other_orders = null;
            
            //add other orders
            foreach ($otherOrders as $keyTwo => $var) {
                //remove duplicate id
                $duplicate_key = array_search($order_tmp['sale'], $var);
                
                if ($duplicate_key != false) {
                    unset($otherOrder[$keyTwo]);
                    $other_orders .= "";
                } else {
                    $dt = new \DateTime($var['order_date']);
                    $other_orders .= $dt->format("d.m.Y") . " " . $var['sale'] . "; ";
                }
            
            }
            
            //permissions
            $permission = Globals::get("orders_permissions");
            
            if ($permission['display_adress_more']['permission'] == 1) {
                //Add more than one adress
                $moreThanOneOrder = Order::displayAdressMore("not_arranged");
                
                $status = array_search($order_tmp['sale'], $moreThanOneOrder);
                
                if ($status != false) {
                    $order_tmp['more_than_one_adress'] = _T("The customer have more than one adress");
                } else {
                    $order_tmp['more_than_one_adress'] = "";
                }
            } else {
                $order_tmp['more_than_one_adress'] = _T("You do not have permissions to see this!");
            }
            
            //order
            $date                            = new \DateTime($order_tmp['scheduled_to_ship_date']); //scheduled to ship date
            $order['sale']                   = $order_tmp['sale'];
            $order['scheduled_to_ship_date'] = $date->format('d.m.Y');
            $order['standart']               = $order_tmp['standart'];
            $order['note_from_bayer']        = $order_tmp['note_from_bayer'];
            $order['note_from_seller']       = $order_tmp['note_from_seller'];
            $date                            = new \DateTime($order_tmp['order_date']); //order date
            $order['order_date']             = $date->format('d.m.Y');
            $order['full_name']              = $order_tmp['full_name'];
            $order['shipping_adress']        = $order_tmp['shipping_adress'];
            $order['more_than_one_adress']   = $order_tmp['more_than_one_adress'];
            $order['new_adress']             = $order_tmp['new_adress'];
            
            //other other
            $order['other_orders'] = $other_orders;
                   
            //shopping cart
            $i = 0;
            foreach ($value['cart'][$order_tmp['sale']] as $key_one => $values) {
                
                $order['shopping_cart'][$i]['sale']     = $values['order_id'];
                $order['shopping_cart'][$i]['name_en']  = $values['name_en'];
                $order['shopping_cart'][$i]['name_bg']  = $values['name_bg'];
                $order['shopping_cart'][$i]['quantity'] = $values['quantity'];
                $order['shopping_cart'][$i]['fabric']   = $values['fabric'];
                $order['shopping_cart'][$i]['color']    = $values['color'];
                $order['shopping_cart'][$i]['size']     = $values['size'];
                $order['shopping_cart'][$i]['sku']      = $values['sku'];
                $order['shopping_cart'][$i]['image']    = $values['image_one'];
                
                //qnt
                $order['quantity'] += $values['quantity'];
                
                $i++;
            }
            
            //generate table
            $tbl = $this->orderTemplate($order, true);
            
            //write table
            $pdf->writeHTML($tbl, true, false, true, false, '');
            //$pdf->addPage();
        }

        $uniqId = uniqid();
        $file   = ROOT_PATH . 'uploads/orders/' . $uniqId . ".pdf";
        
        try {
            //save to file
            $pdf->Output($file, 'F');
        } catch (\Exception $e) {
            $this->logger->logEvent($e->getMessage());
        }
        
        $link = DIR . 'uploads/orders/' . $uniqId . ".pdf";
        
        return $link;
    }
    
    /**
     * Image optimization
     * @author realdark <me@borislazarov.com> on 18 Dec 2014
     * @return string
     */
    private function imgOptimization($url) {
        $error = false;
        
        if ($url === '0') {
            $error = true;
        }
        
        if ($url === "") {
            $error = true;
        }
        
        if ($error == false) {
            
            //file name
            $fileName = explode("/", $url);
            
            //directory
            $dir = ROOT_PATH . "uploads/images/";
            
            //file url
            $file = $dir . "small/" . end($fileName);
            
            if (!file_exists($file)) {
                // Open the file to get existing content
                $data = file_get_contents($url);
                
                // New file
                $new = $dir . "full/" . end($fileName);
                
                // Write the contents back to a new file
                file_put_contents($new, $data);
                
                //ImageWorkshop
                $imgLayer = ImageWorkshop::initFromPath($new);
                
                //resize image layer
                $imgLayer->resizeInPixel(120, 160, null, true);
                
                // Saving the result in a folder
                $imgLayer->save($dir . "small/", end($fileName), true, null, 100);
                
                //delete original(full) file
                unlink($new);
            }
            
            $file = "/uploads/images/small/" . end($fileName);
            
        } else {
            $file = "/uploads/images/small/no_image_available.jpg.300x300_q85_64_32_9_12.jpg";
        }
        
        return $file;
    }
    
    /**
     * Set order empry row height
     * @author realdark <me@borislazarov.com> on 17 Feb 2015
     * @return void
     */
    public function setOrderEmptyRowHeightAjax() {
        Authentication::chechAuthentication("exit");
        
        //Track last action
        User::trackLastAction();

        //Logger
        Log::logMe("Added row height in orders description");
        
        $is_valid = \libraries\gump::is_valid($_POST, array(
            'height'   => 'required|numeric',
            'order_id' => 'required|numeric'
        ));
        
        if($is_valid === true) {
            Util::modal(true, _T("Succes"), _T("The height was set."));
            
            $objOrder = new Order($_POST['order_id']);
            $objOrder->setEmptyRowHeight($_POST['height']);
            
            try {
                $objOrder->save();
            } catch (\Exception $e) {
                \core\logger::exception_handler($e);
            }
        } else {
            Util::modal(true, _T("Error"), $is_valid);
        }
    }

    /**
     * Change item color
     * @author realdark <me@borislazarov.com>
     * @param $orderDate
     * @param $lastDayToShip
     * @return array
     */
    private function getElapsedDays($orderDate, $lastDayToShip) {
        $order  = new \DateTime(date('Y-m-d'));
        $toShip = new \DateTime($lastDayToShip);
        //$toShip->modify('+1 day');

        $dDiff = $order->diff($toShip);

        if ($order->getTimestamp() > $toShip->getTimestamp()) {
            return [
                'color' => 'red',
                'progress' => 100,
                'time_limit' => 'expired'
            ];
        }

        switch($dDiff->days) {
            case 0:
                return [
                    'color' => 'red',
                    'progress' => 100,
                    'time_limit' => 'end day'
                ];

            case 1:
                return [
                    'color' => 'red',
                    'progress' => 87.50,
                    'time_limit' => '1 day left'
                ];
            case 2:
                return [
                    'color' => '#f0ad4e',
                    'progress' => 75,
                    'time_limit' => '2 days left'
                ];
            case 3:
                return [
                    'color' => '#f0ad4e',
                    'progress' => 62.50,
                    'time_limit' => '3 days left'
                ];
            case 4:
                return [
                    'color' => 'green',
                    'progress' => 50,
                    'time_limit' => '4 days left'
                ];
            case 5:
                return [
                    'color' => 'green',
                    'progress' => 37.50,
                    'time_limit' => '5 days left'
                ];
            case 6:
                return [
                    'color' => 'green',
                    'progress' => 25,
                    'time_limit' => '6 days left'
                ];
            case 7:
                return [
                    'color' => 'green',
                    'progress' => 12.5,
                    'time_limit' => '7 days left'
                ];
            default:
                return [
                    'color' => 'blue',
                    'progress' => 0,
                    'time_limit' => '... days left'
                ];
        }
    }

    /**
     * Set product status
     */
    public function productProgress() {
        $productId = $_POST['product_id'];
        $val = $_POST['val'];
        $color = $_POST['color'];
        $order = Request::get('order_id', 'integer');

        //Logger
        Log::logMe("Progress of product with id " . $productId . " was changed to " .$val);
        OrderHistory::add($order, $productId, $val, 11111111111);

        $objProductProgress = new ProductProgress(['product_id' => $productId]);
        $id = $objProductProgress->getId();

        if ($id === null) {
            $objProductProgress = new ProductProgress();
            $objProductProgress->setProductId($productId);
            $objProductProgress->setProgress($val);
            $objProductProgress->setColor($color);

            try {
                $objProductProgress->save();
            } catch (\Exception $e) {
                //
            }
        } else {
            $objProductProgress->setProductId($productId);
            $objProductProgress->setProgress($val);
            $objProductProgress->setColor($color);

            try {
                $objProductProgress->save();
            } catch (\Exception $e) {
                //var_dump($e);
            }
        }

        return 0;
    }

    /**
     * Set product vontrols
     */
    public function productControls() {
        $userId = Request::get('user_id', 'integer');
        $productId = Request::get('product_id', 'integer');
        $action = Request::get('action', 'string');
        $orderId = Request::get('order_id', 'integer');

        Task::createTask($userId, $productId, $action);

        Log::logMe("Set new order assignment.");
        OrderHistory::add($orderId, $productId, $action, $userId);
    }

    /**
     * View order history
     */
    public function orderHistory($id) {
        //If user is not logged send him to loggin page
        Authentication::chechAuthentication("exit");

        //Track last action
        User::trackLastAction();

        $history = OrderHistory::fetch($id);

        //Add information in template
        $this->view->addContent([
            "title"          => _T("Order History"),
            "show_history"   => _T("Show history"),
            "Home"           => _T("Home"),
            "Order"          => _T("Order"),
            "Close"          => _T("Close"),
            "history"        => $history,
            "tasks"          => _T("Tasks")
        ]);

        $jq = "
            //close window
            $('#close').click(function() {
                window.close();
            });
        ";

        $this->view->addContent('jq', $jq);

        $this->view->loadPage("orders/history");
    }

    /**
     * Order feedback
     *
     * @param $id
     */
    public function orderFeedback($id) {
        //If user is not logged send him to loggin page
        Authentication::chechAuthentication("exit");

        //Track last action
        User::trackLastAction();

        $objFeedback = new Feedback(['order_id' => $id]);

        //Add information in template
        $this->view->addContent([
            "title"          => _T("Order Feedback"),
            "show_feedback"  => _T("Show feedback"),
            "Home"           => _T("Home"),
            "Order"          => _T("Order"),
            "Close"          => _T("Close"),
            "id"             => $id,
            'Status'         => _T("Status"),
            "Feedback"       => _T("Feedback"),
            "Good"           => _T("Good"),
            "None"           => _T("None"),
            "Bad"            => _T("Bad"),
            "Save"           => _T("Save"),
            "o_status"       => $objFeedback->getStatus() ? $objFeedback->getStatus() : null,
            "o_feedback"     => $objFeedback->getFeedback() ? $objFeedback->getFeedback() : null
        ]);

        $jq = "
            //close window
            $('#close').click(function() {
                window.close();
            });

            $('#form').submit(function(e) {
                e.preventDefault();
                $.post('/orders/feedback_ajax', $(this).serialize(), function() {
                modal(\"" . _T("Successful") . "\", \"" . _T("The feedback was added!") . "\");
               });
            });
        ";

        $this->view->addContent('jq', $jq);

        $this->view->loadPage("orders/feedback");
    }

    public function orderFeedbackAjax() {
        $orderId = Request::get('id', 'integer');
        $status = Request::get('status', 'integer', 0); //0 - None, 1 - Good, 2 - Bad
        $feedback = Request::get('feedback', 'string');

        $objFeedback = new Feedback(['order_id' => $orderId], ['id']);
        $tmpId = $objFeedback->getId();

        if ($tmpId) {
            $objFeedback = new Feedback($tmpId);
            $objFeedback->setStatus($status);
            $objFeedback->setFeedback($feedback);
            $objFeedback->save();
        } else {
            $objFeedback = new Feedback();
            $objFeedback->setOrderId($orderId);
            $objFeedback->setStatus($status);
            $objFeedback->setFeedback($feedback);
            $objFeedback->save();
        }
    }

    /**
     * Order was checked by user. Ajax
     */
    public function orderCheckedByAjax() {
        $orderId = Request::get('order_id', 'integer');
        $userId = Request::get('user_id', 'integer');

        $objChecked = new OrderCheck(['order_id' => $orderId], ['id']);
        $checkId = $objChecked->getId();

        Log::logMe("The status of order `checked by` was changed.");

        if (isset($checkId)) {
            $objChecked->setUserId($userId);
            $objChecked->save();

            OrderHistory::add($orderId, 0, 'ready', $userId);
        } else {
            $objChecked = new OrderCheck();
            $objChecked->setOrderId($orderId);
            $objChecked->setUserId($userId);
            $objChecked->save();

            OrderHistory::add($orderId, 0, 'ready', $userId);
        }
    }
}