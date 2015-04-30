<?php namespace libraries\phpmailer;
class mail extends phpmailer {
    // Set default variables for all new objects
    public $CharSet    = 'UTF-8';
    public $From       = 'me@borislazarov.com';
    public $FromName   = SITETITLE;
    public $Host       = 'smtp.zoho.com';
    public $Port       = 465;
    public $Mailer     = 'smtp';
    public $SMTPAuth   = true;                         
    public $Username   = 'me@borislazarov.com';                         
    public $Password   = ':p"M8E7s75hB2cq';                         
    public $SMTPSecure = 'ssl';                         
    public $WordWrap   = 75;

    public function subject($subject) {
        $this->Subject = $subject;
    }

    public function body($body) {
        $this->Body = $body;
    }
                         
    public function send() {
        $this->AltBody = strip_tags(stripslashes($this->Body))."\n\n";
        $this->AltBody = str_replace("&nbsp;","\n\n",$this->AltBody);
        return parent::send();
    }


}