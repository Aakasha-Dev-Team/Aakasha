<?php namespace models;
use helpers\session as Session,
    helpers\date as Date,
    helpers\util as Util;

/**
 * Chat model
 * @author realdark <me@borislazarov.com> on 31 Jan 2015
 */
class Chat extends \core\model {
    
    function __construct($key = NULL, $select = []) {
        parent::_construct($key, $select);
    }
    
    /**
     * Init chat
     * @author realdark <me@borislazarov.com> on 31 Jan 2015
     * @return void
     */
    public static function init() {
        
        if (!isset($_SESSION['chatHistory'])) {
            $_SESSION['chatHistory'] = [];
        }
        
        if (!isset($_SESSION['openChatBoxes'])) {
                 $_SESSION['openChatBoxes'] = [];
        }
        
    }
    
    /**
     * Fetch unread messages
     * @author realdark <me@borislazarov.com> on 31 Jan 2015
     * @param string $username  Username
     * @return objects
     */
    public static function fetchMessages($username) {
        $query = self::for_table(PREFIX . "chat")
            ->where([
                'to'   => $username,
                'recd' => 0
            ])
            ->find_many();
            
        return $query;
    }
    
    /**
     * Sanitize chat messages
     * @author realark <me@borislazarov.com> on 31 Jan 2015
     * @param string $text Text for sanitize
     * @return string
     */
    public static function Sanitize($text) {
            $text = htmlspecialchars($text, ENT_QUOTES);
            $text = str_replace("\n\r","\n",$text);
            $text = str_replace("\r\n","\n",$text);
            $text = str_replace("\n","<br>",$text);
            return $text;
    }
    
    /**
     * Fetch recemt users
     * @author realdark <me@borislazarov.com> on 2 Jan 2015
     * @return array
     */
    public static function fetchRecentUsersChat() {
        $objUser = new User();
        $userId  = $objUser->fetchId();
        
        //get username
        $objUser  = new User($userId, ['username']);
        $username = $objUser->getUsername();
        
        //fetch recent chat users
        $query = self::for_table(PREFIX . "chat")
            ->select("from")
            ->select("to")
            ->select("sent")
            ->where_any_is([
                ['from' => $username],
                ['to'   => $username]
            ])
            ->order_by_desc('sent')
            ->find_array();
            
        $recentUsers   = [];
        $recentDate    = [];
        $recentUsers[] = $username;
        
        if (count($query) > 0) {
            //clean and order them in nice array
            foreach ($query as $value) {
                if (array_search($value['from'], $recentUsers) === false) {
                    $recentUsers[]              = $value['from'];
                    $recentDate[$value['from']] = $value['sent'];
                }
                
                if (array_search($value['to'], $recentUsers) === false) {
                    $recentUsers[]            = $value['to'];
                    $recentDate[$value['to']] = $value['sent'];
                }
            }
            
            //remove current logget user
            unset($recentUsers[0]);
            
            //return data
            $data = [];
            
            //fetch avatar and make cirrect array for return
            foreach ($recentUsers as $user) {
                $objUser = new User(['username' => $user], ['id', 'avatar']);
                $avatar  = $objUser->getAvatar();
                
                $data[] = [
                    'username' => $user,
                    'avatar'   => $avatar,
                    'status'   => User::displayStatus($objUser->getId()),
                    'date'     => Date::dateFormat($recentDate[$user], "readable")
                ];
            }
        } else {
            $data = [];
        }
        
        return $data;
    }
    
    /**
     * fetch chat history
     * @author realdark <me@borislazarov.com> on 2 Jan 2015
     * @param string $username Username
     * @return array
     */
    public static function fetchChatHistory($username) {
        $objUser = new User();
        $userId  = $objUser->fetchId();
        
        //get username
        $objUser  = new User($userId, ['username']);
        $currUser = $objUser->getUsername();
        
        //fetch recent chat users
        $query = self::for_table(PREFIX . "chat")
            ->select("from")
            ->select("message")
            ->select("sent")
            ->where_any_is([
                ['from' => $username, 'to'   => $currUser ],
                ['to'   => $username, 'from' => $currUser ]
            ])
            ->order_by_asc('sent')
            ->find_array();
        
        if (count($query) > 0) {    
            //fetch avatar and make cirrect array for return
            foreach ($query as $key => $user) {
                $objUser = new User(['username' => $user['from']], ['avatar', 'id']);
                $avatar  = $objUser->getAvatar();
                
                //Encrypt user id
                $encryptedId = Util::encryptDecryptInt("encrypt", $objUser->getId());
                
                $query[$key]['user_id'] = $encryptedId;
                $query[$key]['avatar']  = $avatar;
                $query[$key]['sent']    = Date::dateFormat($user['sent'], "readable");
            }
        } else {
            $query = [];
        }
        
        return $query;
    }
    
    /**
     * fetch new messages
     * @author realdark <me@borislazarov.com> on 3 Jan 2015
     * @param string $username Username
     * @return array
     */
    public static function fetchNewMessages($username) {
        $objUser = new User();
        $userId  = $objUser->fetchId();
        
        //get username
        $objUser  = new User($userId, ['username']);
        $currUser = $objUser->getUsername();
        
        //fetch recent chat users
        $query = self::for_table(PREFIX . "chat")
            ->select("id", "chat_id")
            ->select("from")
            ->select("message")
            ->select("sent")
            ->where([
                'from' => $username,
                'to'   => $currUser
            ])
            ->where("recd", 0)
            ->order_by_asc('sent')
            ->find_array();
        
        if (count($query) > 0) {  
            //fetch avatar and make cirrect array for return
            foreach ($query as $key => $user) {
                $objUser = new User(['username' => $user['from']], ['avatar', 'id']);
                $avatar  = $objUser->getAvatar();
                
                //Encrypt user id
                $encryptedId = Util::encryptDecryptInt("encrypt", $objUser->getId());
                
                $query[$key]['user_id'] = $encryptedId;
                $query[$key]['avatar']  = $avatar;
                $query[$key]['sent']    = Date::dateFormat($user['sent'], "readable");
                
                //mark as read
                $objChat = new Chat($user['chat_id']);
                $objChat->setRecd(1);
                
                try {
                    $objChat->save();
                } catch (\Exception $e) {
                    \core\logger::exception_handler($e);
                }
            }
        } else {
            $query = [];
        }
        
        return $query;
    }
    
    /**
     * Delete chat history
     * @author realdark <me@borislazarov.com> on 7 Feb 2015
     * @param $days Older tha days
     * @return void
     */
    public function deleteChatHistory($days) {
        
        $delete = self::for_table(PREFIX . "chat")
            ->select('sent')
            ->where_raw('sent < (NOW() - INTERVAL ? DAY)', [$days])
            ->where('recd', 1)
            ->delete_many();
            
    }
}