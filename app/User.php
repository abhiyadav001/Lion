<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use URL;
use DB;
use App;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */

    public function imageUpload($imageBase64Data, $uploadDir)
    {
        $base_url = URL::to('/');
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $image_data = base64_decode($imageBase64Data);
        $time = md5(microtime());
        $file = $uploadDir . $time . '.png';
        file_put_contents($file, $image_data);
        $img_hash = $base_url . '/img/profile/' . $time . '.png';
        return $img_hash;
    }

    public function insert($data)
    {
        $user = new User();
        if (isset($data['user_id'])) {
            $user = App\User::find($data['user_id']);
        }
        if (isset($data['fb_id'])) {
            $user->fb_id = $data['fb_id'];
        }
        if (isset($data['age'])) {
            $user->age = $data['age'];
        }
        if (isset($data['gender'])) {
            $user->gender = $data['gender'];
        }
        if (isset($data['occupation'])) {
            $user->occupation = $data['occupation'];
        }
        if (isset($data['name'])) {
            $user->name = $data['name'];
        }
        if (isset($data['city'])) {
            $user->city = $data['city'];
        }
        if (isset($data['img_hash'])) {
            $user->img_hash = $data['img_hash'];
        }
        if (isset($data['email'])) {
            $user->email = $data['email'];
        }
        $user->save();
        return $user;
    }

    public function blockUser($data)
    {
        $blockUser = DB::table('users_block_info')->insert(
            ['user_fb_id' => $data['user_fb_id'], 'blocked_user_fb_id' => $data['blocked_user_fb_id'], 'created_at' => date('Y-m-d H:i:s'), "updated_at" => date('Y-m-d H:i:s')]);
        return $blockUser;
    }

    public function isUserAlreadyBlocked($data)
    {
        if (DB::table('users_block_info')->where("user_fb_id", '=', $data['user_fb_id'])->where("blocked_user_fb_id", '=', $data['blocked_user_fb_id'])->select('id')->get()) {
            return true;
        }
        return false;
    }

    public function discoverUsers($data)
    {
        $fbId = $data['fb_id'];
        $lat = $data['lat'];
        $lng = $data['lng'];
        $hour = ($data['hour']) ? $data['hour'] : 12;
        $new_time = date("Y-m-d H:i:s", strtotime("-$hour hours"));
        $sqlEdit = '';
        if ((isset($data['age_from']) && !empty($data['age_from'])) && (isset($data['age_to']) && !empty($data['age_to']))) {
            $sqlEdit = " and u.age>='" . $data['age_from'] . "' and u.age<='" . $data['age_to'] . "' ";
        }
        if (isset($data['gender']) && !empty($data['gender'])) {
            $sqlEdit .= " and u.gender='" . $data['gender'] . "' ";
        }
        return DB::select(" SELECT TIMESTAMPDIFF(HOUR,`last_signup`,CURRENT_TIMESTAMP()) as last_seen_before, u.id as user_id, u.*, ubi.id, udd.lat, udd.lng, DATE_FORMAT(udd.last_signup,'%h:%i:%s %p') as last_seen_time, ( 3959 * acos( cos( radians( $lat ) ) * cos( radians( udd.lat ) ) * cos( radians( udd.lng ) - radians( $lng) ) + sin( radians( $lat ) ) * sin( radians( udd.lat ) ) ) ) AS miles_distance  from users as u inner join users_device_details as udd on u.id=udd.user_id left join users_block_info as ubi on (u.fb_id=ubi.blocked_user_fb_id and ubi.user_fb_id=$fbId)  left join users_block_info as ubib on (u.fb_id=ubib.user_fb_id and ubib.blocked_user_fb_id=$fbId) where u.fb_id!=$fbId and u.visibility='on' and ubi.id is null and ubib.id is null and last_signup>'" . $new_time . "' $sqlEdit having miles_distance <= 5 ");
    }

    public function getUserDetail($userId)
    {
        return App\User::find($userId);
    }

    public function getUsersForSendNotification($data)
    {
        $fbId = $data['fb_id'];
        $lat = $data['lat'];
        $lng = $data['lng'];
        $msg = $data['message'];

        $sqlEdit = "";
        if ((isset($data['age_from']) && !empty($data['age_from'])) && (isset($data['age_to']) && !empty($data['age_to']))) {
            $sqlEdit = " and u.age>='" . $data['age_from'] . "' and u.age<='" . $data['age_to'] . "' ";
        }
        if (isset($data['gender']) && !empty($data['gender'])) {
            $sqlEdit .= " and u.gender='" . $data['gender'] . "' ";
        }
        return DB::select(" SELECT u.id as user_id, u.*, ubi.id, ubib.id, udd.lat, udd.lng, udd.token, '$msg' as message, ( 3959 * acos( cos( radians( $lat ) ) * cos( radians( udd.lat ) ) * cos( radians( udd.lng ) - radians( $lng) ) + sin( radians( $lat ) ) * sin( radians( udd.lat ) ) ) ) AS miles_distance  from users as u inner join users_device_details as udd on u.id=udd.user_id left join users_block_info as ubi on (u.fb_id=ubi.blocked_user_fb_id and ubi.user_fb_id=$fbId) left join users_block_info as ubib on (u.fb_id=ubib.user_fb_id and ubib.blocked_user_fb_id=$fbId) where u.fb_id!=$fbId and u.visibility='on' and ubi.id is null and ubib.id is null $sqlEdit having miles_distance < 5 ");
    }

    public function sendPushNotification($senderData, $receiverData)
    {
        // My private key's passphrase here:
        $passphrase = 'linkup';

        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', 'linkUpAPNSDistr.pem');
        stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

        // Open a connection to the APNS server
        $fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);

        //    if (!$fp)
        //        exit("Failed to connect: $err $errstr" . PHP_EOL);
        //
        //    echo 'Connected to APNS' . PHP_EOL;
        // Create the payload body
        $body['aps'] = array('sound' => 'default', 'senderData' => $senderData, 'alert' => $receiverData->message);

        // Encode the payload as JSON
        $payload = json_encode($body);

        // Build the binary notification
        $deviceToken = str_replace(' ', '', $receiverData->token);

        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

        // Send it to the server
        fwrite($fp, $msg, strlen($msg));

        //    if (!$result)
        //        echo 'Error, notification not sent' . PHP_EOL;
        //    else
        //        echo 'notification sent!' . PHP_EOL;
        // Close the connection to the server
        fclose($fp);
        //
        //    return $result;
        /*
         * save in temporary table not required on production
         */
        //DB::insert("insert into check_notification (sender_id, receiver_id, device_token, message) values ($senderData->id, $receiverData->user_id, $receiverData->token, '$receiverData->message')");
    }

    public function getUserIdAttachedWithFbId($fbId)
    {
        $userId = DB::table('users')->where('fb_id', $fbId)->pluck('id');
        return $userId;
    }

    public function changeUserVisibility($data)
    {
        DB::table('users')->where('id', $data['user_id'])->update(['visibility' => $data['visibility']]);
    }

    public function updateUserSocialNetworks($data, $onField, $onValue)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        DB::table('users')->where($onField, $onValue)->update($data);
    }

    public function getFullDetails($lat, $lng, $radius, $fbIdArray)
    {
        return DB::select(DB::raw("SELECT users.*,users_device_details.lat,users_device_details.lng, ( 3959 * acos( cos( radians( $lat ) ) * cos( radians( users_device_details.lat ) ) * cos( radians( users_device_details.lng ) - radians( $lng ) ) + sin( radians( $lat ) ) * sin( radians( users_device_details.lat ) ) ) ) AS distance
        FROM users inner join users_device_details on users.id = users_device_details.user_id
        WHERE users.visibility = 'on'
        and users.fb_id not in ($fbIdArray)
        HAVING distance < $radius
        ORDER BY distance asc
        LIMIT 0 , 20"));
    }

    public function checkBlockList($fbId, $frndFbId)
    {
        $userId = DB::table('users_block_info')
            ->orWhere(function ($query) use ($fbId, $frndFbId) {
                $query->where('user_fb_id', $frndFbId)->where('blocked_user_fb_id', $fbId);
            })
            ->orWhere(function ($query) use ($fbId, $frndFbId) {
                $query->where('user_fb_id', $fbId)->where('blocked_user_fb_id', $frndFbId);
            })
            ->get();
        return $userId;
    }
}