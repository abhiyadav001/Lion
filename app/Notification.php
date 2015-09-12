<?php

namespace App;
use DB;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';


    public function sendConfNotification($data, $deviceToken, $id)
    {
        DB::table('notifications')->where('id', $id)->update(['status' => $data['status']]);
        $passphrase = 'wuumz2';

        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', 'wuumz2.pem');
        stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

        $fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);

        $body['aps'] = array('alert' => $data['message'], 'sound' => 'default',
            'sender_id' => $data['sender_id'], 'sender_name' => $data['sender_name'], 'lat' => $data['lat'], 'lng' => $data['lng']);

        $payload = json_encode($body);

        $deviceToken = str_replace(' ', '', $deviceToken);
        $deviceToken = str_replace('-', '', $deviceToken);
        $deviceToken = str_replace('<', '', $deviceToken);
        $deviceToken = str_replace('>', '', $deviceToken);
        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
        fwrite($fp, $msg, strlen($msg));

        fclose($fp);
    }

    public function sendPushNotification($data, $deviceToken)
    {
        $notificationInfo = $this->saveNotification($data);
        $passphrase = 'wuumz2';

        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', 'wuumz2.pem');
        stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

        $fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);

        $body['aps'] = array('alert' => $data['message'], 'sound' => 'default',
            'sender_id' => $data['sender_id'], 'sender_name' => $data['sender_name'], 'notifyId' => @$notificationInfo->id, 'exp_at' => @$notificationInfo->expired_at);

        $payload = json_encode($body);

        $deviceToken = str_replace(' ', '', $deviceToken);
        $deviceToken = str_replace('-', '', $deviceToken);
        $deviceToken = str_replace('<', '', $deviceToken);
        $deviceToken = str_replace('>', '', $deviceToken);
        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
        fwrite($fp, $msg, strlen($msg));

        fclose($fp);
    }

    public function saveNotification($data)
    {
        $notify = new Notification();
        $notification_responce_time = '30';
        $time = date('Y-m-d H:i:s');
        $newtime = strtotime("$time + $notification_responce_time seconds");
        $expire = date('Y-m-d H:i:s', $newtime);
        $notify->sender_id = $data['sender_id'];
        $notify->reciver_id = $data['reciver_id'];
        $notify->message = $data['message'];
        $notify->expired_at = $expire;
        $notify->save();
        return $notify;
    }

}
