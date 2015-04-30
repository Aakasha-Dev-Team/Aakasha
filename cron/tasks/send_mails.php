<?php
require_once ("/home/aakashac/app/cron/db/db.php");
require_once ("/home/aakashac/app/cron/libraries/class.phpmailer.php");

function body($recipient) {
    $body = "
        <!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
        
                        
        <html><head><meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
        <meta name='viewport' content='width=device-width; initial-scale=1.0; maximum-scale=1.0;'>
        <title>Aakasha</title>
        
        <style type='text/css'>
        
        div, p, a, li, td { -webkit-text-size-adjust:none; }
        
        *{
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        }
        
        .ReadMsgBody
        {width: 100%; background-color: #000000;}
        .ExternalClass
        {width: 100%; background-color: #000000;}
        body{width: 100%; height: 100%; background-color: #000000; margin:0; padding:0; -webkit-font-smoothing: antialiased;}
        html{width: 100%;}
        
        @font-face {
            font-family: 'proxima_novalight';src: url('http://rocketway.net/themebuilder/template/templates/gear/font/proximanova-light-webfont.eot');src: url('http://rocketway.net/themebuilder/template/templates/gear/font/proximanova-light-webfont.eot?#iefix') format('embedded-opentype'),url('http://rocketway.net/themebuilder/template/templates/gear/font/proximanova-light-webfont.woff') format('woff'),url('http://rocketway.net/themebuilder/template/templates/gear/font/proximanova-light-webfont.ttf') format('truetype');font-weight: normal;font-style: normal;}
        
        @font-face {
            font-family: 'proxima_nova_rgregular'; src: url('http://rocketway.net/themebuilder/template/templates/gear/font/proximanova-regular-webfont.eot');src: url('http://rocketway.net/themebuilder/template/templates/gear/font/proximanova-regular-webfont.eot?#iefix') format('embedded-opentype'),url('http://rocketway.net/themebuilder/template/templates/gear/font/proximanova-regular-webfont.woff') format('woff'),url('http://rocketway.net/themebuilder/template/templates/gear/font/proximanova-regular-webfont.ttf') format('truetype');font-weight: normal;font-style: normal;}
        
        @font-face {
            font-family: 'proxima_novasemibold';src: url('http://rocketway.net/themebuilder/template/templates/gear/font/proximanova-semibold-webfont.eot');src: url('http://rocketway.net/themebuilder/template/templates/gear/font/proximanova-semibold-webfont.eot?#iefix') format('embedded-opentype'),url('http://rocketway.net/themebuilder/template/templates/gear/font/proximanova-semibold-webfont.woff') format('woff'),url('http://rocketway.net/themebuilder/template/templates/gear/font/proximanova-semibold-webfont.ttf') format('truetype');font-weight: normal;font-style: normal;}
            
        @font-face {
                font-family: 'proxima_nova_rgbold';src: url('http://rocketway.net/themebuilder/template/templates/gear/font/proximanova-bold-webfont.eot');src: url('http://rocketway.net/themebuilder/template/templates/gear/font/proximanova-bold-webfont.eot?#iefix') format('embedded-opentype'),url('http://rocketway.net/themebuilder/template/templates/gear/font/proximanova-bold-webfont.woff') format('woff'),url('http://rocketway.net/themebuilder/template/templates/gear/font/proximanova-bold-webfont.ttf') format('truetype');font-weight: normal;font-style: normal;
        
        }
        
        p {padding: 0!important; margin-top: 0!important; margin-right: 0!important; margin-bottom: 0!important; margin-left: 0!important; }
        
        
        .hover:hover {
                opacity:0.85;
                filter:alpha(opacity=85);
                
        }
        
        .jump:hover {
                opacity:0.75;
                filter:alpha(opacity=75);
                padding-top: 10px!important;
                
        }
        
        
        .fullImage img {width: 600px; height: auto; text-align: center;}
        .fullImg img {width: 185px; height: auto;}
        .fullImg260 img {width: 260px; height: auto;}
        .col3 img {width: 185px; height: auto;}
        .img50 img {width: 147px; height: auto;}
        </style>
        
        <!-- @media only screen and (max-width: 640px) 
                           {*/
                           -->
        <style type='text/css'> @media only screen and (max-width: 640px){
                        body{width:auto!important;}
                        
                        table[class=full] {width: 100%!important; clear: both; }
                        table[class=mobile] {width: 100%!important; padding-left: 20px; padding-right: 20px; clear: both; }
                        table[class=fullCenter] {width: 100%!important; text-align: center!important; clear: both; }
                        td[class=textCenter] {width: 100%!important; text-align: center!important; clear: both; }
                        .erase {display: none;}
                        .headerBG {background-position: center center!important;}
                        table[class=fullImg] {width: 100%!important; text-align: center!important; clear: both; }
                        .fullImg img {width: 100%!important; text-align: center!important; clear: both; }
                        table[class=fullImg260] {width: 100%!important; text-align: center!important; clear: both; }
                        .fullImg260 img {width: 100%!important; text-align: center!important; clear: both; }
                        table[class=col3] {width: 100%; text-align: center!important; clear: both;}
                        table[class=fullImage] {width: 100%!important; height: auto!important; text-align: center!important; clear: both; }
                        .fullImage img {width: 100%!important; height: auto!important; text-align: center!important; clear: both; }
                        table[class=img50] {width: 50%!important;}
                        .img50 img {width: 100%!important;}
                        .w20 {width: 10px!important;}
                        td[class=wordBreak] {-ms-word-break: break-all; word-break: break-all; word-break: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto;}
                        
        } </style>
        <!--
                        
                        
        @media only screen and (max-width: 479px) 
                           {
                           -->
        <style type='text/css'> @media only screen and (max-width: 479px){
                        body{width:auto!important;}
                        
                        table[class=full] {width: 100%!important; clear: both; }
                        table[class=mobile] {width: 100%!important; padding-left: 20px; padding-right: 20px; clear: both; }
                        table[class=fullCenter] {width: 100%!important; text-align: center!important; clear: both; }
                        td[class=textCenter] {width: 100%!important; text-align: center!important; clear: both; }
                        table[class=fullImage] {width: 100%!important; height: auto!important; text-align: center!important; clear: both; }
                        .fullImage img {width: 100%!important; height: auto!important; text-align: center!important; clear: both; }
                        table[class=mobCenter] {width: 100%!important; clear: both; }
                        td[class=mobCenter] {width: 100%!important; text-align: center!important; clear: both; }
                        .mobErase {display: none;}
                        td[class=font30] {font-size: 30px!important; letter-spacing: 10px!important; padding: 0px!important;}
                        table[class=col3] {width: 100%; text-align: center!important; clear: both;}
                        .col3 img {width: 100%!important; text-align: center!important; clear: both;}
                        table[class=fullImg] {width: 100%!important; text-align: center!important; clear: both; }
                        .fullImg img {width: 100%!important; text-align: center!important; clear: both; }
                        table[class=fullImg260] {width: 100%!important; text-align: center!important; clear: both; }
                        .fullImg260 img {width: 100%!important; text-align: center!important; clear: both; }
                        .headerBG {background-position: center center!important;}
                        table[class=img50] {width: 50%!important;}
                        .img50 img {width: 100%!important;}
                        .hide {display: inherit!important;}
                        td[class=wordBreak] {-ms-word-break: break-all; word-break: break-all; word-break: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto;}
                        
                        }
                        
        } </style>
        
        </head>
        <body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
        
        <table width='100%' border='0' cellpadding='0' cellspacing='0' align='center' class='full'>
                <tbody><tr>
                        <td>
                                <table width='600' border='0' cellpadding='0' cellspacing='0' align='center' class='mobile'>
                                        <tbody><tr>
                                                <td width='100%'>
                                                        <div class='sortable_inner ui-sortable'>
                                                        </div>
                                                </td>
                                        </tr>
                                </tbody></table>
                        </td>
                </tr>
        </tbody></table>
        
        
        <table width='100%' border='0' cellpadding='0' cellspacing='0' align='center' class='full' bgcolor='#030303' style='background-color: rgb(3, 3, 3); position: relative; z-index: 0;'>
                <tbody><tr>
                        <td style=\"background-image: url('https://pbs.twimg.com/profile_banners/536353915/1413185321/1500x500'); -webkit-background-size: cover; background-size: cover; background-position: center center; background-repeat: no-repeat;\" class='headerBG' id='BGheaderChange'>
                                <table width='600' border='0' cellpadding='0' cellspacing='0' align='center' class='full'>
                                        <tbody><tr>
                                                <td width='100%'>
                                                        <table width='100%' border='0' cellpadding='0' cellspacing='0' align='center' class='mobile'>
                                                                <tbody><tr>
                                                                        <td width='100%' height='120'></td>
                                                                </tr>
                                                                <tr>
                                                                        <td width='100%' valign='middle'>
                                                                                <div class='sortable_inner ui-sortable'>
                                                                                        <table width='600' border='0' cellpadding='0' cellspacing='0' align='center' style='text-align: center;' class='fullCenter' object='drag-module-small'>
                                                                                                <tbody><tr>
                                                                                                        <td valign='middle' width='100%' style='text-align: center; font-family: proxima_nova_rgregular, 'Myriad Pro', Helvetica, Arial, sans-serif; font-size: 16px; color: #000; letter-spacing: 4px;'>
                                                                                                                
                                                                                                        </td>
                                                                                                </tr>
                                                                                        </tbody></table>
                                                                                        <table width='600' border='0' cellpadding='0' cellspacing='0' align='center' style='text-align: center;' class='fullCenter' object='drag-module-small'>
                                                                                                <tbody><tr>
                                                                                                        <td width='100%' height='25'></td>
                                                                                                </tr>
                                                                                        </tbody></table>
                                                                                        <table width='600' border='0' cellpadding='0' cellspacing='0' align='center' style='text-align: center;' class='fullCenter' object='drag-module-small'>
                                                                                                <tbody><tr>
                                                                                                        <td valign='middle' width='100%' style='text-align: center; font-family: proxima_nova_rgregular, 'Myriad Pro', Helvetica, Arial, sans-serif; font-size: 16px; color: #000; letter-spacing: 4px;'>
                                                                                                        </td>
                                                                                                </tr>
                                                                                        </tbody></table>
                                                                                        <table width='600' border='0' cellpadding='0' cellspacing='0' align='center' style='text-align: center;' class='fullCenter' object='drag-module-small'>
                                                                                                <tbody><tr>
                                                                                                        <td width='100%' height='40'></td>
                                                                                                </tr>
                                                                                        </tbody></table>
                                                                                        <table width='600' border='0' cellpadding='0' cellspacing='0' align='center' style='text-align: center;' class='fullCenter' object='drag-module-small'>
                                                                                                <tbody><tr>
                                                                                                        <td width='100%' height='120'></td>
                                                                                                </tr>
                                                                                        </tbody></table>
                                                                                </div>
                                                                        </td>
                                                                </tr>
                                                        </tbody></table>
                                                        
                                                        
                                                        <div class='sortable_inner ui-sortable'>
                                                                
                                                                
                                                                <table width='600' border='0' cellpadding='0' cellspacing='0' align='center' bgcolor='#454d54' class='full' object='drag-module-small' style='background-color: rgb(69, 77, 84);' cu-identifier='element_041883390536531806'>
                                                                        <tbody><tr>
                                                                                <td width='100%' height='15'>
                                                                                </td>
                                                                        </tr>
                                                                        <tr>
                                                                                <td width='100%' valign='middle'>
                                                                                        
                                                                                        
                                                                                        <table width='30' border='0' cellpadding='0' cellspacing='0' align='left' style='border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;' class='w20'>
                                                                                                <tbody><tr>
                                                                                                        <td width='100%' height='1'>
                                                                                                        </td>
                                                                                                </tr>
                                                                                        </tbody></table>
                                                                                        
                                                                                        
                                                                                        <table width='265' border='0' cellpadding='0' cellspacing='0' align='left' style='border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt; text-align: center; text-transform: uppercase;' class='mobCenter'>
                                                                                                <tbody><tr>
                                                                                                        <td height='20' valign='middle' width='25%'>
                                                                                                                <a href='https://www.etsy.com/shop/Aakasha' style='text-decoration: none; color: #fff !important; font-family: proxima_novasemibold, 'Myriad Pro', Helvetica, Arial, sans-serif; font-size: 13px;' cu-identify='element_019506350439041853'>ETSY SHOP</a>
                                                                                                        </td>
                                                                                                        <td height='20' valign='middle' width='25%'>
                                                                                                                <a href='https://marketplace.asos.com/boutique/aakasha-boutique' style='text-decoration: none; color: rgb(255, 255, 255); font-family: proxima_novasemibold, 'Myriad Pro', Helvetica, Arial, sans-serif; font-size: 13px; text-transform: uppercase;' cu-identify='element_03731253033038229'>ASOS Boutique</a>
                                                                                                        </td>
                                                                                                </tr>
                                                                                        </tbody></table>
                                                                                        
                                                                                        
                                                                                        <table width='1' border='0' cellpadding='0' cellspacing='0' align='right' style='border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;' class='w20'>
                                                                                                <tbody><tr>
                                                                                                        <td width='100%' height='20'>
                                                                                                        </td>
                                                                                                </tr>
                                                                                        </tbody></table>
                                                                                        
                                                                                        
                                                                                        <table width='265' border='0' cellpadding='0' cellspacing='0' align='right' style='border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt; text-align: center; text-transform: uppercase;' class='mobCenter'>
                                                                                                <tbody><tr>
                                                                                                        <td height='20' valign='middle' width='50%'>
                                                                                                                <a href='https://www.facebook.com/aakashastyle' style='text-decoration: none; color: rgb(255, 255, 255); font-family: proxima_novasemibold, 'Myriad Pro', Helvetica, Arial, sans-serif; font-size: 13px; text-transform: uppercase;' cu-identify='element_02043069126084447'>FACEBOOK</a>
                                                                                                        </td>
                                                                                                        <td height='20' valign='middle' width='50%'>
                                                                                                                <a href='https://www.pinterest.com/aakasha/' style='text-decoration: none; color: rgb(255, 255, 255); font-family: proxima_novasemibold, 'Myriad Pro', Helvetica, Arial, sans-serif; font-size: 13px; text-transform: uppercase;' cu-identify='element_07265586368739605'>PINTEREST</a>
                                                                                                        </td>
                                                                                                </tr>
                                                                                        </tbody></table>
                                                                                        
                                                                                </td>
                                                                        </tr>
                                                                        <tr>
                                                                                <td width='100%' height='15'>
                                                                                </td>
                                                                        </tr>
                                                                </tbody></table>
                                                                <div style='display: none' id='element_0538133330643177'></div>
                                                                
                                                        </div>
                                                        
                                                </td>
                                        </tr>
                                </tbody></table>
                        </td>
                </tr>
        </tbody></table>
        <table width='100%' border='0' cellpadding='0' cellspacing='0' align='center' class='full' style='position: relative; z-index: 0;' cu-identifier='element_023836468276567757'>
                <tbody><tr>
                        <td width='100%' valign='top' style='background-color: rgb(246, 246, 246);'>
                                <table width='600' border='0' cellpadding='0' cellspacing='0' align='center' class='mobile'>
                                        <tbody><tr>
                                                <td>
                                                        <table width='100%' border='0' cellpadding='0' cellspacing='0' align='center' class='full'>
                                                                <tbody><tr align='center'>
                                                                        <td width='100%' height='30'></td>
                                                                </tr>
                                                        </tbody></table>
                                                        <table width='100%' border='0' cellpadding='0' cellspacing='0' align='center' class='full'>
                                                                <tbody><tr align='center'>
                                                                        <td width='100%' height='30'>
                                                                                <span style='text-decoration: none; color: #464e55; font-family: 'proxima_novasemibold', Myriad Pro, Helvetica, Arial, sans-serif; font-size: 22px; text-align:center; text-transform:uppercase'>Dear " . $recipient . "!</span>
                                                                                <br>
                                                                        </td>
                                                                </tr>
                                                        </tbody></table>
                                                        <table width='586' bgcolor='#ffffff' border='0' cellpadding='0' cellspacing='0' align='left' style='border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;' class='fullCenter'>
                                                                <tbody><tr>
                                                                        <td width='100%' height='10'></td>
                                                                </tr>
                                                                <tr>
                                                                        <td width='100%' valign='middle'>
                                                                                <table width='586' border='0' cellpadding='0' cellspacing='0' align='center' class='full'>
                                                                                        <tbody><tr>
                                                                                                <td width='15' height='44'></td>
                                                                                                <td width='10' height='44' class='erase'></td>
                                                                                                <td width='586' height='44' align='center' style='text-decoration: none; color: #464e55; font-family: 'proxima_novasemibold', Myriad Pro, Helvetica, Arial, sans-serif; font-size: 14px; padding-right:15px;'>
                                                                                                        <span><br>	
                                                                                                                My name is Milena and It is so nice to meet you here:)
                                                                                                                Thank you for your order!<br><br>
                                                                                                                I will send you shipping notification once your order is shipped!<br><br>
                                                                                                                Share you special moments with your special someone in the most special and unique way!<br><br>
                                                                                                                I hope to love your new garment/s as much as I do !<br><br>
                                                                                                                You can sign up <a href='eepurl.com/H-ZZ1'>here</a> for new designs,sales and promotions<br><br>
                                                                                                                Thank you so very much!<br><br>
                                                                                                        </span>
                                                                                                </td>
                                                                                        </tr>
                                                                                </tbody></table>
                                                                        </td>
                                                                </tr>
                                                                <tr>
                                                                        <td width='100%' height='10'></td>
                                                                </tr>
                                                        </tbody></table>
                                                        <table width='100%' border='0' cellpadding='0' cellspacing='0' align='center' class='full'>
                                                                <tbody><tr align='center'>
                                                                        <td width='100%' height='30'>
                                                                                <span style='text-decoration: none; color: #464e55; font-family: 'proxima_novasemibold', Myriad Pro, Helvetica, Arial, sans-serif; font-size: 14px; text-align:center'>
                                                                                        <br>Be Happy and Dare to Wear...
                                                                                </span>
                                                                                <br>
                                                                                <span style='text-decoration: none; color: #464e55; font-family: 'proxima_novasemibold', Myriad Pro, Helvetica, Arial, sans-serif; font-size: 17px; text-align:center'>Love A.</span>
                                                                        </td>
                                                                </tr>
                                                        </tbody></table>
                                                        <table width='100%' border='0' cellpadding='0' cellspacing='0' align='center' class='full'>
                                                                <tbody><tr align='center'>
                                                                        <td width='100%' height='30'></td>
                                                                </tr>
                                                        </tbody></table>
                                                </td>
                                        </tr>
                                </tbody></table>
                        </td>
                </tr>
        </tbody></table>
        
        
        
        <table width='100%' border='0' cellpadding='0' cellspacing='0' align='left' style='border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;' class='full'>
                <tbody><tr>
                        <td width='100%' height='30'></td>
                </tr>
        </tbody></table>
        <table width='100%' border='0' cellpadding='0' cellspacing='0' align='center' class='full' style=\"background-image: url('https://img1.etsystatic.com/016/0/6822943/isa_760xN.4774041967_69q0.jpg'); background-position: center center; background-repeat:no-preat; -webkit-background-size: cover; -moz-background-size: cover; -o-background-size: cover; background-size: cover; background-repeat: repeat-x;\" cu-identifier='element_08802049495279789'>
                <tbody><tr>
              <td class='headerBG' id='banner3Change'>
                                <table width='600' border='0' cellpadding='0' cellspacing='0' align='center' class='mobile'>
                                        <tbody><tr>
                                                <td width='100%'>
                                                        <table width='100%' border='0' cellpadding='0' cellspacing='0' align='center' class='full'>
                                                                <tbody><tr>
                                                                        <td width='100%' valign='middle'>
                                                                                <table width='600' border='0' cellpadding='0' cellspacing='0' align='center' style='text-align: center;' class='fullCenter'>	
                                                                                        <tbody><tr>
                                                                                                <td valign='middle' width='100%' style='text-align: center; font-family: proxima_nova_rgbold, 'Myriad Pro', Helvetica, Arial, sans-serif; font-size: 30px; letter-spacing: 12px; color: rgb(255, 255, 255); text-transform: uppercase; line-height: 40px;' class='wordBreak'>
                                                                                                        <br><br><span style='font-family: 'proxima_novalight', Myriad Pro, Helvetica, Arial, sans-serif;'>Love</span> A.♥<br><br>
                                                                                                </td>
                                                                                        </tr>
                                                                                </tbody></table>							
                                                                        </td>
                                                                </tr>
                                                        </tbody></table>
                                                </td>
                                        </tr>
                                </tbody></table>
                        </td>
                </tr>
        </tbody></table>
        <table width='100%' border='0' cellpadding='0' cellspacing='0' align='center' class='full'>
                <tbody><tr>
                        <td width='100%' valign='top' bgcolor='#454d54' style='background-color: rgb(69, 77, 84);'>
        
                                <table width='600' border='0' cellpadding='0' cellspacing='0' align='center' class='mobile'>
                                        <tbody><tr>
                                                <td width='100%' height='30'></td>
                                        </tr>
                                        <tr>
                                                <td width='100%'>
                                                        <table width='600' border='0' cellpadding='0' cellspacing='0' align='center' style='border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;' class='fullCenter'>
                                                                <tbody><tr>
                                                                        <td width='100%' style='text-align: center;'>
                                                                                <a href='https://twitter.com/aakasha2203' style='text-decoration: none;'><img src='http://rocketway.net/themebuilder/template/templates/gear/images/social_icon1.png' width='35' height='35px;' alt='' border='0' style='width: 35px; height: 35px;' cu-identify='element_09229098043870181'></a>
                                                                                &nbsp;&nbsp;<span class='mobErase'>&nbsp;&nbsp;&nbsp;</span>
                                                                                <a href='https://www.facebook.com/aakashastyle/' style='text-decoration: none;'><img src='http://rocketway.net/themebuilder/template/templates/gear/images/social_icon2.png' width='35' height='35px;' alt='' border='0' style='width: 35px; height: 35px;'></a>
                                                                                &nbsp;&nbsp;<span class='mobErase'>&nbsp;&nbsp;&nbsp;</span>
                                                                                <a href='https://plus.google.com/117581804252038631871/' style='text-decoration: none;'><img src='http://rocketway.net/themebuilder/template/templates/gear/images/social_icon3.png' width='35' height='35px;' alt='' border='0' style='width: 35px; height: 35px;'></a>
                                                                                &nbsp;&nbsp;<span class='mobErase'>&nbsp;&nbsp;&nbsp;</span>
                                                                        </td>
                                                                </tr>
                                                        </tbody></table>
        
                                                        <table width='100' border='0' cellpadding='0' cellspacing='0' align='right' style='border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;' class='full'>
                                                                <tbody><tr>
                                                                        <td width='100%' height='1'>									
                                                                        </td>
                                                                </tr>
                                                        </tbody></table>							
                                                </td>
                                        </tr>
                                </tbody></table>
                                <table width='100%' border='0' cellpadding='0' cellspacing='0' align='center' class='full'>
                                        <tbody><tr>
                                                <td width='100%' height='30'>									
                                                </td>
                                        </tr>
                                </tbody></table>
                        </td>
                </tr>
        </tbody></table>
        <table width='100%' border='0' cellpadding='0' cellspacing='0' align='center' class='full'>
                <tbody><tr>
                        <td width='100%' valign='top'>
                                <table width='600' border='0' cellpadding='0' cellspacing='0' align='center' class='mobile'>
                                        <tbody><tr>
                                                <td width='100%' height='20'></td>
                                        </tr>
                                        <tr>
                                                <td width='100%'>
                                                        <table width='250' border='0' cellpadding='0' cellspacing='0' align='left' style='border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;' class='fullCenter'>
                                                                <tbody><tr>
                                                                        <td height='60' width='100%' style='font-size: 12px; color: rgb(150, 150, 150); text-align: left; font-family: proxima_novasemibold, Helvetica, Arial, sans-serif; line-height: 24px; vertical-align: middle;' class='textCenter'>	
                                                                                <p cu-identify='element_05511883022263646'>© 2015 All rights Reserved</p>
                                                                        </td>
                                                                </tr>
                                                        </tbody></table>
                                                        <table width='340' border='0' cellpadding='0' cellspacing='0' align='right' style='border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt; text-align: right;' class='fullCenter'>	
                                                                <tbody><tr>
                                                                        <td height='60' valign='middle' width='33%' style='font-size: 12px; color: #969696; text-align: center; font-family: 'proxima_novasemibold', Helvetica, Arial, sans-serif; line-height: 24px; vertical-align: middle;'>
                                                                                <a href='https://www.etsy.com/shop/Aakasha' style='text-decoration: none; color: rgb(150, 150, 150);'>ETSY SHOP</a>
                                                                        </td>
                                                                        <td height='60' valign='middle' width='33%' style='font-size: 12px; color: #969696; text-align: center; font-family: 'proxima_novasemibold', Helvetica, Arial, sans-serif; line-height: 24px; vertical-align: middle;'>
                                                                                <a href='https://marketplace.asos.com/boutique/aakasha-boutique' style='text-decoration: none; color: rgb(150, 150, 150);'>ASOS BOUTIQUE</a>
                                                                        </td>
                                                                        <td height='60' valign='middle' width='33%' style='font-size: 12px; color: #969696; text-align: center; font-family: 'proxima_novasemibold', Helvetica, Arial, sans-serif; line-height: 24px; vertical-align: middle;'>
                                                                                <a href='https://www.facebook.com/aakashastyle' style='text-decoration: none; color: rgb(150, 150, 150);'>FACEBOOK</a>
                                                                        </td>
                                                                </tr>
                                                        </tbody></table>
                                                                                                                        
                                                </td>
                                        </tr>
                                </tbody></table>
                        </td>
                </tr>
        </tbody></table>
                <style type='text/css'>body{ background: none !important; } </style></body></html>	<style type='text/css'>body{ background: none !important; } </style>
    ";
    
    return $body;
}


$query = $db->query("SELECT id, email, name FROM app_emails WHERE sent=0")
or die("could not connect");

$mail           = new PHPMailer;
$mail->From     = 'no-replay@aakasha.com';
$mail->FromName = 'Aakasha Ltd.';
$mail->Subject  = 'Welcome to Aakasha webstore';
$mail->isHTML(true);  

if ($query->num_rows) {
    while ($emails = $query->fetch_object()) {
        $current = clone $mail;
        
        $current->Body = body($emails->name);
        $current->addAddress($emails->email, $emails->name);
        $current->send();
        
        //update status
        $db->query("UPDATE app_emails SET sent=1 WHERE id=" . $emails->id)
        or die("could not connect");
    }
}
?>