<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class UserLinkupLog extends Model
{
    protected $table = 'users_linkup_log';

    public function logLinkupPersonalChat($data)
    {
        $this->sender_fb_id = $data['sender_fb_id'];
        $this->receiver_fb_id = $data['receiver_fb_id'];
        $this->message = $data['message'];
        $this->save();
    }

    /*
     * This function return last linkup of user
     */
    public function showLinkupLog($data)
    {
        if(!isset($data['last_linkup']) || empty($data['last_linkup'])){
            $data['last_linkup'] = 10;
        }
        return DB::select("select u.*, MAX(ull.created_at) as msg_sent_on  from users_linkup_log as ull inner join users as u on u.fb_id = ull.receiver_fb_id where sender_fb_id = ".$data['fb_id']." group by ull.receiver_fb_id order by msg_sent_on desc limit ".$data['last_linkup']);

    }
}
