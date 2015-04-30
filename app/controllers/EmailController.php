<?php namespace controllers;
use helpers\util as Util,
    helpers\request as Request,
    helpers\url as URL,
    helpers\Globals as Globals,
    models\Authentication as Authentication,
    models\User as User,
    models\Email as Email;
use models\Log;

/**
 * EmailController controller
 * @author realdark <me@borislazarov.com> on 11 Feb 2015
 */
class EmailController extends \core\controller {

    public function __construct() {
        parent::__construct();
    }

    /**
    * call the parent construct
    */ 
    public function uploadEmails() {
        //If user is not logged send him to loggin page
        Authentication::chechAuthentication("exit");
        
        //Track last action
        User::trackLastAction();
        
        //permissions
        $permission = Globals::get("orders_permissions");
        
        if ($permission['upload_customers']['permission'] == 0) {
            header('Location: /');
        }
        
        //Add information in template
        $this->view->addContent([
            "title"          => _T("Emails"),
            "Import"         => _T("Import"),
            "Export"         => _T("Export"),
            "Orders listing" => _T("Orders listing"),
            "File input"     => _T("File input"),
            "Submit"         => _T("Submit"),
            "Show Details"   => _T("Show Details"),
            "Upload files"   => _T("Upload files"),
            "Upload emails"  => _T("Upload emails"),
            "Upload emails file"   => _T("Upload emails file")
        ]);
        
        //jQuery
        $jq = "
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
                    url: '/emails/upload_emails_ajax',
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
        $this->view->loadPage("emails/upload_emails");
    }
    
    /**
     * Upload files action
     * @author realdark <me@borislazarov.com> on 11 Feb 2015
     * @return json
     */
    public function uploadEmailsAjax() {
        //If user is not logged send him to loggin page
        Authentication::chechAuthentication("exit");
        
        //Track last action
        User::trackLastAction();

        //Logger
        Log::logMe("Uploaded file with emails");
        
        $objEmail = new Email();
        
        //Upload file to server
        $file     = $objEmail->uploadFile($_FILES);
        
        if ($file == true) {
            $status = $objEmail->importEmailsToDB();
            
            Util::modal(true, _T("Success"), _T("The file was successfully uploaded."));
        } else {
            //File was not uploaded msg
            Util::modal(false, _T("Error"), _T("The file was NOT uploaded."));
        }
    }
 
     /* Commented out text for auto mails */
        
    //    <b>We are sorry for the inconviniensce if you already received this message but it didn't make it out properly to every receiver. Please ignore if you've already received it.</b>
        
    //    Welcome to Aakasha
        
    //    My name is Milena and It is so nice to meet you here:)
        
    //    Thank you for your order!
        
    //    I will send you shipping notification once your order is shipped!
        
    //    I hope to love your new garment/s as much as I do !
        
    //    You can sign up <a href ="eepurl.com/H-ZZ1">here</a> for new designs,sales and promotions 
        
    //    from our Brand        
        
    //    Thank you so very much!
        
    //    Be Happy and Dare to Wear...
        
    //    Love
    //    A.
    
}