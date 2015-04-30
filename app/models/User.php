<?php namespace models;
use helpers\Session as Session,
    helpers\date as Date,
    helpers\url as URL;

/**
 * User model
 * @author
 */
class User extends \core\model {
    
    function __construct($key = NULL, $select = []) {
        parent::_construct($key, $select);
    }

    /**
     * Fetch user id if exist
     * @author Bobi <me@borislazarov.com>
     * @internal param int $id User id
     * @return mixed|null|string
     */
    public function fetchId() {
            
        if (isset($this->id)) {
            $id = $this->id;
        } else {
                
            $sessionId = Session::get('user_id');
            
            if ($sessionId != false) {
                $id = $sessionId;
            } else {
                $id = NULL;
            }

        }
        
        return $id;

    }
    
    /**
     * Fetch users
     * @author realdark <me@borislazarov.com> on 20 Jan 2015
     * @param integer $search Search by Department
     * @return array
     */
    public function fetchUsers($search = NULL) {
        $query = self::for_table(PREFIX . "users");
        
        if (isset($search) && $search != 0) {
            $query->where('department_id', $search);
        }
        
        $data = $query->find_array();
            
        return $data;
    }
    
    /**
     * Track last action
     * @author realdark <me@borislazarov.com> on 3 Jan 2015
     * @return void
     */
    public static function trackLastAction() {
    $objUser = new User();
    $userId  = $objUser->fetchId();
            
        if ($userId != NULL) {
            $objUser = new User($userId);
            $objUser->set_expr('last_action', 'NOW()');
            
            try {
                $objUser->save();
            } catch (\Exception $e) {
                \core\logger::exception_handler($e);
            }
        }
    }
    
    /**
     * Display status - online or offline
     * @author realdark <me@borislazarov.com> on 3 Jan 2015
     * @return string
     */
    public static function displayStatus($userId) {
        $objUser = new User($userId, ['last_action']);
            
        //status
        //$icon = "1422988849_metacontact_offline.png";
        $icon = "1422989760_status-offline.png";
        
        //last action
        $dt 	    = new \DateTime($objUser->getLastAction());
        $userLastAction = $dt->getTimestamp();
        
        //get +10 min time
        $dt->modify('+10 minutes');
        $featureTime = $dt->getTimestamp();
        
        //get current time
        $dt	      = new \DateTime();
        $currTime = $dt->getTimestamp();
        
        if ($userLastAction <= $currTime && $featureTime >= $currTime) {
            //$icon = "1422988837_metacontact_online.png";
            $icon = "1422989753_status-online.png";
        }
        
        $data = "<img src='" . URL::get_template_path() . "img/status/" . $icon . "' alt='" . _T("Status") . "'>";
        
        return $data;
    }
    
    public static function fetchUsersChat() {
        $data = [];
        
        $query = self::for_table(PREFIX . "users")
            ->find_array();
            
        foreach ($query as $user) {
            $objUser = new User($user['id'], ['last_action', 'username', 'avatar']);
            
            //last action
            $dt 	    = new \DateTime($objUser->getLastAction());
            $userLastAction = $dt->getTimestamp();
            
            //get +10 min time
            $dt->modify('+10 minutes');
            $featureTime = $dt->getTimestamp();
            
            //get current time
            $dt	      = new \DateTime();
            $currTime = $dt->getTimestamp();
            
            if ($userLastAction <= $currTime && $featureTime >= $currTime) {
                $data['online'][] = [
                    'username'  => $objUser->getUsername(),
                    'last_seen' => Date::dateFormat($objUser->getLastAction(), 'hours'),
                    'avatar'    => $objUser->getAvatar()
                ];
            } else {
                $data['offline'][] = [
                    'username'  => $objUser->getUsername(),
                    'last_seen' => Date::dateFormat($objUser->getLastAction(), 'hours'),
                    'avatar'    => $objUser->getAvatar()
                ];
            }
        }
        
        return $data;
    }
    
}