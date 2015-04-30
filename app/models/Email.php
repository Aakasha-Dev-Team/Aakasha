<?php namespace models;
    use helpers\util as Util;
/**
 * Email model
 * @author realdark <me@borislazarov.com> on 11 Feb 2015
 */
class Email extends \core\model {
    
    function __construct($key = NULL, $select = []) {
        parent::_construct($key, $select);
    }
    
    private $uploadedFile;
    
    /**
     * Upload file
     * @author Bobi <me@borislazarov.com> on 6 Dec 2014
     * @return boolean
     */
    public function uploadFile($file) {
        $uploaddir     = ROOT_PATH . "uploads/emails/";
        $uploadfile    = $uploaddir . basename($_FILES[0]['name']);
        $fileExtension = explode(".", $_FILES[0]['name']);
        $allowedtypes  = ["csv"];
        
        if (in_array(end($fileExtension), $allowedtypes)) {
            if (move_uploaded_file($_FILES[0]['tmp_name'], $uploadfile)) {
                $result = true;
            } else {
                $result = false;
            }
        }
        
        $this->uploadedFile = new \StdClass;
        $this->uploadedFile->type = end($fileExtension);
        $this->uploadedFile->url  = $uploadfile;
        
        return $result;
    }
    
    /**
     * Import emails to db
     * @author realdark <me@borislazarov.com> on 11 Feb 2015
     * @return string
     */
    public function importEmailsToDB() {
        $filename = $this->uploadedFile->url;
        $fileType = $this->uploadedFile->type;
        
        $csv = Util::csv_to_array($filename, ";");
        
        foreach ($csv as $row) {
            $objEmail = new Email(['email' => $row['buyer_email']], ['email']);
            $email    = $objEmail->getEmail();
            
            if (!isset($email)) {
                $objEmail = new Email();
                $objEmail->setEmail($row['buyer_email']);
                $objEmail->setOrderId($row['orderid']);
                $objEmail->setName($row['name']);
                $objEmail->setCountry($row['country']);
                
                //fix date... Because Ico don`t know how to export!!!!!!
                $arrDate = explode(" ", $row['creation_time']);
                $date    = $arrDate[0] . " " . $arrDate[2];
                $dt      = new \DateTime(str_replace(".", "-", $date));
                
                $objEmail->setDate($dt->format('Y-m-d H:i:s'));
                
                try {
                    $objEmail->save();
                } catch (\Exception $e) {
                    \core\logger::exception_handler($e);
                }
                
            }
        }
        
        return true;
    }
    
}