<?php

namespace App;

use DB;

use Illuminate\Database\Eloquent\Model;

class DeviceDetails extends Model
{
    protected $table = "users_device_details";

    public function insert($data)
    {
        $userId = $data['user_id'];
        $token = $data['token'];
        $type = $data['type'];
        $lat = $data['lat'];
        $lng = $data['lng'];

        $user_id = DB::table('users_device_details')->where('token', $token)->pluck('user_id');

        if (!empty($user_id)) {
            $update = DB::table('users_device_details')->where('token', $token)->update(array('type' => $type, 'lat' => $lat, 'lng' => $lng, 'user_id' => $userId, 'last_signup' => date('Y-m-d H:i:s')));
            return $update;
        } else {
            $this->user_id = $userId;
            $this->type = $type;
            $this->lat = $lat;
            $this->lng = $lng;
            $this->token = $token;
            $this->last_signup = date('Y-m-d H:i:s');
            $this->save();
            return $this;
        }
    }

    public function updateDeviceDetails($data, $onField, $onValue)
    {
        if ((isset($data['lat'])) && ($data['lng'] != '0')){
            $data['updated_at'] = date('Y-m-d H:i:s');
            if (isset($data['device_status'])) {
                $data['last_signup'] = date('Y-m-d H:i:s');
            }
            DB::table('users_device_details')->where($onField, $onValue)->update($data);
        }
    }
}