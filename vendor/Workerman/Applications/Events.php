<?php
use \GatewayWorker\Lib\Gateway;
/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
   /**
    * 当客户端发来消息时触发
    * @param int $client_id 连接id
    * @param mixed $message 具体消息
    */
   public static function onMessage($client_id, $data) {
       $message = json_decode($data, true);
       $message_type = $message['type'];
       switch($message_type) {
           case 'init':
               // uid
               $uid = $message['id'];
               // 设置session
               $_SESSION = array(
                   'username' => $message['username'],
                   'avatar'   => $message['avatar'],
                   'id'       => $uid,
                   'sign'     => $message['sign']
               );
               // 将当前链接与uid绑定
               Gateway::bindUid($client_id, $uid);
               // 通知当前客户端初始化
               $init_message = array(
                   'message_type' => 'init',
                   'id'           => $uid,
               );
               Gateway::sendToClient($client_id, json_encode($init_message));
               return;
           case 'chatMessage':
               // 聊天消息
               $type = $message['data']['to']['type'];
               $to_id = $message['data']['to']['id'];
               $uid = $_SESSION['id'];
 
               $chat_message = array(
                    'message_type' => 'chatMessage',
                    'data' => array(
                        'username' => $_SESSION['username'],
                        'avatar'   => $_SESSION['avatar'],
                        'id'       => $type === 'friend' ? $uid : $to_id,
                        'type'     => $type,
                        'content'  => htmlspecialchars($message['data']['mine']['content']),
                        'timestamp'=> time()*1000,
                    )
               );
               switch ($type) {
                   // 私聊
                   case 'friend':
                       return Gateway::sendToUid($to_id, json_encode($chat_message));
                   // 群聊
                   case 'group':
                       return Gateway::sendToGroup($to_id, json_encode($chat_message), $client_id);
               }
               return;
           case 'hide':
           case 'online':
               $status_message = array(
                   'message_type' => $message_type,
                   'id'           => $_SESSION['id'],
               );
               $_SESSION['online'] = $message_type;
               Gateway::sendToAll(json_encode($status_message));
               return;
           case 'ping':
               return;
           default:
               echo "unknown message $data" . PHP_EOL;
       }
   }
   
   /**
    * 当用户断开连接时触发
    * @param int $client_id 连接id
    */
   public static function onClose($client_id) {
       $logout_message = array(
           'message_type' => 'logout',
           'id'           => $_SESSION['id']
       );
       Gateway::sendToAll(json_encode($logout_message));
   }
}