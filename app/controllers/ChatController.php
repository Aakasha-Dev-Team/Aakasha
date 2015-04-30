<?php namespace controllers;
use models\Chat as Chat,
    models\User as User,
    models\Authentication as Authentication,
    libraries\gump as GUMP,
    helpers\util as Util,
    helpers\url as URL,
    helpers\escape as Escape;
use models\Log;

/**
 * Chat controller
 * @author realdark <me@borislazarov.com> on 31 Jan 2015
 */
class ChatController extends \core\controller {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Main chat function
     * @author realdark <me@borislazarov.com> on 31 Jan 2015
     * @return void
     */
    public function chatHeartbeat() {
        //Track last action
        User::trackLastAction();
        
        $objUser = new User();
        $userId  = $objUser->fetchId();
        
        //get username
        $objUser  = new User($userId, ['username']);
        $username = $objUser->getUsername();
        
        if (Authentication::chechAuthentication("status") == 0) {
            return true;
        }
        
        $objChat = Chat::fetchMessages($username);
        
        $result    = [];
        $chatBoxes = [];
        
        foreach($objChat as $chat) {
            if (!isset($_SESSION['openChatBoxes'][$chat->getFrom()]) && isset($_SESSION['chatHistory'][$chat->getFrom()])) {
                    $result['items'][] = $_SESSION['chatHistory'][$chat->getFrom()];
            }
            
            $message = $chat->getMessage();
            
            $chat->setMessage(Chat::Sanitize($message));
            
            $result['items'][] = [
                's' => 0,
                'f' => $chat->getFrom(),
                'm' => $chat->getMessage()
            ];
            
            if (!isset($_SESSION['chatHistory'][$chat->getFrom()])) {
                    $_SESSION['chatHistory'][$chat->getFrom()] = "";
            }
            
            $_SESSION['chatHistory'][$chat->getFrom()][] = [
                's' => 0,
                'f' => $chat->getFrom(),
                'm' => $chat->getMessage()
            ];
            
            unset($_SESSION['tsChatBoxes'][$chat->getFrom()]);
            $_SESSION['openChatBoxes'][$chat->getFrom()] = $chat->getSent();
        }
        
        if (!empty($_SESSION['openChatBoxes'])) {
            foreach ($_SESSION['openChatBoxes'] as $chatbox => $time) {
                if (!isset($_SESSION['tsChatBoxes'][$chatbox])) {
                    $now = time()-strtotime($time);
                    $time = date('g:iA M dS', strtotime($time));

                    $message = "Sent at $time";
                    if ($now > 180) {
                        $result['items'][] = [
                            's' => 2,
                            'f' => $chatbox,
                            'm' => $message
                        ];
                        
                        if (!isset($_SESSION['chatHistory'][$chatbox])) {
                                $_SESSION['chatHistory'][$chatbox] = "";
                        }
                        
                        $_SESSION['chatHistory'][$chatbox][] = [
                            's' => 2,
                            'f' => $chatbox,
                            'm' => $message
                        ];
                        
                        $_SESSION['tsChatBoxes'][$chatbox] = 1;
                    }
                }
            }
        }
        
        $objChat = Chat::fetchMessages($username);
        
        foreach($objChat as $chat) {
            $chat->setRecd(1);
            
            try {
                $chat->save();
            } catch (\Exception $e) {
                \core\logger::exception_handler($e);
            }
        }
        
        if (count($result) == 0) {
            $result['items'] = "";
        }
        
        echo json_encode($result);
    }
    
    /**
     * Chat session
     * @author realdark <me@borislazarov.com> on 31 Jan 2015
     * @return string
     */
    private function chatBoxSession($chatbox) {
            
        $items = '';
        
        if (isset($_SESSION['chatHistory'][$chatbox])) {
                $items = $_SESSION['chatHistory'][$chatbox];
        }

        return $items;
    }
    
    /**
     * Start chat session
     * @author realdark <me@borislazarov.com> on 31 Jan 2015
     * @return json
     */
    public function startChatSession() {
        //Track last action
        User::trackLastAction();
        
        $objUser = new User();
        $userId  = $objUser->fetchId();
        
        //get username
        $objUser  = new User($userId, ['username']);
        $username = $objUser->getUsername();
        
        $items      = [];
        $mergeItems = [];
        
	if (!empty($_SESSION['openChatBoxes']) && count($_SESSION['openChatBoxes']) > 1) {
            foreach ($_SESSION['openChatBoxes'] as $chatbox => $void) {
                $items      = $this->chatBoxSession($chatbox);
                $mergeItems = array_merge($mergeItems, $items);
            }
	} elseif (!empty($_SESSION['openChatBoxes'])) {
            foreach ($_SESSION['openChatBoxes'] as $chatbox => $void) {
                $mergeItems = $this->chatBoxSession($chatbox);
            }
        }
        
        $result['username'] = $username;
        $result['items']    = $mergeItems;
        
        echo json_encode($result);
    }
    
    /**
     * Send chat
     * @author realdark <me@borislazarov.com> on 31 Jan 2015
     * @return integer
     */
    public function sendChat() {
        //Track last action
        User::trackLastAction();

        //Logger
        Log::logMe("Send chat message");
        
        $objUser = new User();
        $userId  = $objUser->fetchId();
        
        //get username
        $objUser  = new User($userId, ['username']);
        $username = $objUser->getUsername();
        
        $from    = $username;
        $to      = $_POST['to'];
        $message = $_POST['message'];

        $_SESSION['openChatBoxes'][$_POST['to']] = date('Y-m-d H:i:s', time());

        $messagesan = Chat::Sanitize($message);

        if (!isset($_SESSION['chatHistory'][$_POST['to']])) {
            $_SESSION['chatHistory'][$_POST['to']] = [];
        }
        
        $_SESSION['chatHistory'][$_POST['to']][] = [
            's' => 1,
            'f' => $to,
            'm' => $messagesan
        ];
        
        unset($_SESSION['tsChatBoxes'][$_POST['to']]);
        
        $objChat = new Chat();
        $objChat->setFrom($from);
        $objChat->setTo($to);
        $objChat->setMessage($message);
        $objChat->set_expr('sent', 'NOW()');
        
        try {
            $objChat->save();
        } catch (\Exception $e) {
            \core\logger::exception_handler($e);
        }
        
        echo "1";
    }
    
    /**
     * Close chat
     * @author realdark <me@borislazarov.com> on 31 Jan 2015
     * @return integer
     */
    public function closeChat() {
        //Track last action
        User::trackLastAction();
        
        unset($_SESSION['openChatBoxes'][$_POST['chatbox']]);
        
        echo "1";
    }
    
    public function fetchStatus() {
        $username = $_POST['username'];
        
        $objUser = new User(['username' => $username], ['id']);
        $userId  = $objUser->getId();
        
        echo User::displayStatus($userId);
    }
    
    /**
     * Chat profile page
     * @author realdark <me@borislazarov.com> on 2 Jan 2015
     * @return void
     */
    public function chatHome($slug) {
        //track last action
        User::trackLastAction();
        
        //Logged user
        $loggedUser = Authentication::chechAuthentication("exit");
        
        //Template text
        $this->view->addContent([
            'title'                => _T("Chat"),
            'Home'                 => _T("Home"),
            'Back'                 => _T("Back"),
            'Chat'                 => _T("Chat"),
            'RECENT CHAT HISTORY'  => _T("RECENT CHAT HISTORY"),
            'User profile'         => _T("User profile"),
            'Enter Message'        => _T("Enter Message"),
            'SEND'                 => _T("SEND"),
            'RECENT CHATS'         => _T("RECENT CHATS")
        ]);
        
        $objUser = new User();
        $userId  = $objUser->fetchId();
        
        //get username and avatar
        $objUser        = new User($userId, ['id', 'username', 'avatar']);
        $currUserName   = $objUser->getUsername();
        $currUserAvatar = $objUser->getAvatar();
        
        //Encrypt user id
        $encryptedId = Util::encryptDecryptInt("encrypt", $objUser->getId());
        
        //user history
        $user = Escape::html($slug);
        
        //Fetch recent users
        $recentUsers = Chat::fetchRecentUsersChat();
        
        //Add users table
        $this->view->addContent("users", $recentUsers);
        
        //if user is 0 load first user from table else load selected user
        if ($user == "0") {
            $chatHistory = Chat::fetchChatHistory($recentUsers[0]['username']);
            $fromUser    = $recentUsers[0]['username'];
        } else {
            $chatHistory = Chat::fetchChatHistory($user);
            $fromUser    = $user;
        }
        
        //From user
        $this->view->addContent("from_user", $fromUser);
        
        //Add users table
        $this->view->addContent("chat_history", $chatHistory);
        
        //JavaScript
        //$this->view->addContent('js', "<script src='" . URL::get_template_path() . "js/jquery.validate.min.js'></script>");
        $this->view->addContent('js', "
            <script src='//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.13.1/jquery.validate.min.js'></script>\n
            <script src='" . URL::get_template_path() . "js/datetime.min.js'></script>
        ");
        
        //jQuery
        $jq = "
            //turn off live window
            enableChat = false;
            
            //load container bottom
            $('#history-container').scrollTop($('#history-container').prop('scrollHeight'));
            
            //Validate Sign Up Form
            var messageform = $('#messageform').validate({
                rules: {
                    message: {
                        required: true,
                        minlength: 1,
                        maxlength: 1500
                    },
                },
                highlight: function(element) {
                    $(element).closest('.input-group').addClass('has-error');
                },
                unhighlight: function(element) {
                    $(element).closest('.input-group').removeClass('has-error');
                },
                errorElement: 'span',
                errorClass: 'help-block',
                errorPlacement: function(error, element) {
                    if(element.parent('.input-group').length) {
                        error.insertAfter(element.parent());
                    } else {
                        error.insertAfter(element);
                    }
                }
            });
            
            //send message
            $('#send-message').click(function(event) {
                event.preventDefault();
                var status  = messageform.form();
                var content = $('#messageform').serialize();
                var message = $('[name=\"message\"]').val();
                
                if (status == true) {
                    $.post('/chat/send_message_action', content, function(data) {
                        if (data.status == false) {
                            modal(data.title, data.body);
                        } else {
                            $('[name=\"message\"]').val('');
                        }
                        
                        load.hide();
                    }, 'json');
                    
                    //add my messages
                    var myDate = new Date();
                    
                    $('#history-container').append('\
                        <li class=\'media\'>\
                            <div class=\'media-body\'>\
                                <div class=\'media\'>\
                                    <a class=\'pull-left\' href=\'#\'>\
                                        <img class=\'media-object\' style=\'height:55px; width: 55px;\' src=\'/uploads/avatars/" . $currUserAvatar . "\'>\
                                    </a>\
                                    <div class=\'media-body\'>\
                                        ' + message + '\
                                        <br>\
                                        <small class=\'text-muted\'><a href=\'/profile/display/" . $encryptedId . "\' title=\'User profile\'>" . $currUserName . "</a> | ' + myDate.format('d/m/Y H:i') + '</small>\
                                        <hr>\
                                    </div>\
                                </div>\
                            </div>\
                        </li>\
                    ');
                    
                    //load container bottom
                    $('#history-container').scrollTop($('#history-container').prop('scrollHeight'));
                } else {
                    load.hide();
                }
            });
            
            //receive new message on every 3 seconds
            var fromuser = $('[name=\"fromuser\"]').val();
            
            setInterval(function() {
                $.post('/chat/recieve_messages_action', {
                    fromuser : fromuser
                }, function(data) {
                    if (data != '') {
                        $('#history-container').append(data);
                        
                        //load container bottom
                        $('#history-container').scrollTop($('#history-container').prop('scrollHeight'));
                    }
                });
            }, 3000);
            
            //update users on every 5 minutes
            setInterval(function() {
                var empty = '';
                var users = '';
            
                $.post('/chat/recent_users_chat', {
                    empty : empty
                }, function(data) {
                    $.each(data, function(key, value) {
                        users += '\
                            <li class=\"media\">\
                                <div class=\"media-body\">\
                                    <div class=\"media\">\
                                        <a class=\"pull-left\" href=\"/chat/chat_home/' + value.username + '\">\
                                            <img class=\"media-object\" style=\"max-height:40px; width: 40px\" src=\"/uploads/avatars/' + value.avatar + '\" alt=\"User\">\
                                        </a>\
                                        <div class=\"media-body\" >\
                                            <p>' + value.status + '<a href=\"/chat/chat_home/' + value.username + '\" title=\"Begin chat\">' + value.username + '</a></p>\
                                            <small class=\"text-muted\">' + value.date + '</small>\
                                        </div>\
                                    </div>\
                                </div>\
                            </li>\
                        ';
                    });
                    
                    //add users in template
                    $('#users').empty().html(users);
                }, 'json');
            }, 300000);
        ";
        
        //Add jq in template
        $this->view->addContent("jq", $jq);
        
        //Add page
        $this->view->loadPage('frontend/profile/chat/chat');
    }
    
    /**
     * Send message from UI page
     * @author realdark <me@borislazarov.com> on 3 Jan 2015
     * @return json
     */
    public function sendMessageAction() {
        $gump = new GUMP();
        
        $_POST = $gump->sanitize($_POST); // You don't have to sanitize, but it's safest to do so.

        $gump->validation_rules(array(
            'fromuser' => 'required',
            'message'  => 'required|max_len,1500|min_len,1'
        ));

        $gump->filter_rules(array(
            'fromuser' => 'trim|sanitize_string',
            'message'  => 'trim|sanitize_string'
        ));

        $validated_data = $gump->run($_POST);

        if($validated_data === false) {
            Util::modal(false, _T("Error"), $gump->get_readable_errors(true));
        } else {
            $objUser = new User();
            $userId  = $objUser->fetchId();
            
            //get username
            $objUser  = new User($userId, ['username']);
            $currUser = $objUser->getUsername();
            
            $objChat = new Chat();
            $objChat->setTo($validated_data['fromuser']);
            $objChat->setFrom($currUser);
            $objChat->setMessage($validated_data['message']);
            $objChat->set_expr('sent', 'NOW()');
            
            try {
                $objChat->save();
            } catch (\Exception $e) {
                \core\logger::exception_handler($e);
            }
            
            Util::modal(true);
        }
    }
    
    /**
     * Fetch unread messages from IU
     * @author realdark <me@borislazarov.com> on 3 Jan 2015
     * @return string
     */
    public function recieveMessagesAction() {
        $gump = new GUMP();
        
        $_POST = $gump->sanitize($_POST); // You don't have to sanitize, but it's safest to do so.

        $gump->validation_rules(array(
            'fromuser' => 'required'
        ));

        $gump->filter_rules(array(
            'fromuser' => 'trim|sanitize_string'
        ));

        $validated_data = $gump->run($_POST);

        if($validated_data !== false) {
            $newMessages = Chat::fetchNewMessages($validated_data['fromuser']);
            
            $data = "";
            
            if (count($newMessages) > 0) {
                foreach ($newMessages as $message) {
                    $data .= "
                        <li class=\"media\">
                            <div class=\"media-body\">
                                <div class=\"media\">
                                    <a class=\"pull-left\" href=\"#\">
                                        <img class=\"media-object\" style=\"height:55px; width: 55px;\" src=\"/uploads/avatars/" . $message['avatar'] . "\">
                                    </a>
                                    <div class=\"media-body\" >
                                        " . Escape::html($message['message']) . "
                                        <br>
                                        <small class=\"text-muted\"><a href=\"/profile/display/" . $message['user_id'] . "\" title=\"User profile\">" . $message['from'] . "</a> | " . $message['sent'] . "</small>
                                        <hr>
                                    </div>
                                </div>
                            </div>
                        </li>
                    ";
                }
            }
            
            echo $data;
        }
    }
    
    /**
     * Recent users chat
     * @author realdark <me@borislazarov.com> on 3 Jan 2015
     * @return josn
     */
    public function recentUsersChat() {
        //Fetch recent users
        $recentUsers = Chat::fetchRecentUsersChat();
        
        echo json_encode($recentUsers);
    }
}