<?php

namespace App\Http\Controllers;

use App\DeviceDetails;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Validator;
use App\User;
use Illuminate\Support\Facades\Input;

class UserController extends Controller
{

    private $uploaddir;

    public function __construct()
    {
        $this->setUploadDir();
    }

    protected function setUploadDir()
    {
        return $this->uploaddir = public_path() . '/img/profile/';
    }

    public function checkValidation()
    {
        return $validator = Validator::make(
            array(
                'fb_id' => Input::get('fb_id'),
                'img_hash' => Input::get('img_hash'),
                'age' => Input::get('age'),
                'gender' => Input::get('gender'),
                'name' => Input::get('name')
            ), array(
                'fb_id' => 'required',
                'img_hash' => 'required',
                'age' => 'required',
                'gender' => 'required',
                'name' => 'required'
            )
        );
    }

    public function checkValidationForProfileUpdate()
    {
        return $validator = Validator::make(
            array(
                'fb_id' => Input::get('fb_id')
            ), array(
            'fb_id' => 'required'
        ));
    }

    public function errorMessage($msg)
    {
        return json_encode(array(
                'success' => false,
                'messages' => $msg,
                'response' => Null), 400
        );
    }

    public function successMessage($msg)
    {
        return json_encode(array(
                'success' => true,
                'messages' => $msg,
                'response' => Null), 400
        );
    }

    public function successMessageWithVar($msg, $userUpdated)
    {
        return json_encode(array(
                'success' => true,
                'messages' => $msg,
                'response' => array(
                    'users' => $userUpdated)
            ), 200
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $user = new User();
        $data = Input::all();
        $users = $user->discoverUsers($data);
        $msg = "Discovered user list.";
        return $this->successMessageWithVar($msg, $users);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $user = new User();
        $deviceDetail = new DeviceDetails();
        $data = Input::all();
        $validator = $this->checkValidation();
        if ($validator->fails()) {
            $messages = $validator->messages();
            foreach ($messages->all() as $message) {
                $msg[] = $message;
            }
            return $this->errorMessage($msg);
        }
        //check if user exist with same fb_id then update user info instead of insert new entry
        $userId = $user->getUserIdAttachedWithFbId($data['fb_id']);
        if ($userId) {
            $data['user_id'] = $userId;
        }
        $img_hash = $user->imageUpload($data['img_hash'], $this->uploaddir);
        if (isset($img_hash) and !empty($img_hash)) {
            $data['img_hash'] = $img_hash;
        }
        $userProfile = $user->insert($data);
        $data['user_id'] = $userProfile->id;
        $deviceDetail->insert($data);
        $msg = "Profile saved successfully.";
        return $this->successMessageWithVar($msg, $userProfile);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        $user = new User();
        $userDetail = $user->getUserDetail($id);
        $msg = "User details.";
        return $this->successMessageWithVar($msg, $userDetail);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id)
    {
        $data = Input::all();
        $user = new User();
        $deviceDetail = new DeviceDetails();
        $validator = $this->checkValidationForProfileUpdate();
        if ($validator->fails()) {
            $messages = $validator->messages();
            foreach ($messages->all() as $message) {
                $msg[] = $message;
            }
            return $this->errorMessage($msg);
        }
        if (isset($data['img_hash']) and !empty($data['img_hash'])) {
            $imageBase64Data = $data['img_hash'];
            $img_hash = $user->imageUpload($imageBase64Data, $this->uploaddir);
            if (isset($img_hash) and !empty($img_hash)) {
                $data['img_hash'] = $img_hash;
            }
        }
        $data['user_id'] = $id;
        $userProfile = $user->insert($data);
        $deviceDetail->insert($data);
        $msg = "Profile updated successfully.";
        return $this->successMessageWithVar($msg, $userProfile);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    public function updateDeviceStatus($id)
    {
        $data = Input::all();
        if ((isset($data['lat'])) && ($data['lng'] == '0')) {
            $msg = "Device detail not updated.";
            return $this->errorMessage($msg);
        }
        $data['user_id'] = $id;
        $deviceDetail = new DeviceDetails();
        $deviceDetail->updateDeviceDetails($data, "token", $data['token']);
        $msg = "Device detail updated successfully.";
        return $this->successMessage($msg);
    }

    public function blockUsers($id)
    {
        $data = Input::all();
        $data['user_id'] = $id;
        $user = new User();
        if (!$user->isUserAlreadyBlocked($data)) {
            $user->blockUser($data);
        }
        $msg = "User blocked successfully.";
        return $this->successMessage($msg);
    }

    public function sendNotification($id)
    {
        $inputData = Input::all();
        $inputData['user_id'] = $id;
        $user = new User();
        $senderDetails = $user->getUserDetail($id);
        $receiversInfo = $user->getUsersForSendNotification($inputData);
        foreach ($receiversInfo as $receiverInfo) {
            $user->sendPushNotification($senderDetails, $receiverInfo);
        }
        $msg = "Message sent successfully.";
        return $this->successMessage($msg);
    }

    public function testNotification()
    {
        //echo date('Y-m-d H:i:s');exit;
        $passphrase = 'linkup';
        $deviceToken = "c6f3392cfd71fa1e285b4483a4c23d10e851b87510b013b41f36788fabb0e83e";
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
        $body['aps'] = array('sound' => 'default', 'alert' => "hi hello");

        // Encode the payload as JSON
        $payload = json_encode($body);

        // Build the binary notification
        $deviceToken = str_replace(' ', '', $deviceToken);

        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
        // Send it to the server
        fwrite($fp, $msg, strlen($msg));

        /* if (!$result)
                 echo 'Error, notification not sent' . PHP_EOL;
             else
                echo 'notification sent!' . PHP_EOL;*/
        // Close the connection to the server
        fclose($fp);

    }

    public function changeUserVisibility($id)
    {
        $data = Input::all();
        $data['user_id'] = $id;
        $user = new User();
        $user->changeUserVisibility($data);
        $msg = "User visibility change successfully.";
        return $this->successMessage($msg);
    }

    public function updateUserSocialNetwork($id)
    {
        $data = Input::all();
        $user = new User();
        $user->updateUserSocialNetworks($data, "id", $id);
        $msg = "User social network detail updated successfully.";
        return $this->successMessage($msg);
    }

    public function getFullDetail($fbID, $lat, $lng)
    {
        $user = new User();
        $data = Input::all();

        $totalData = count($data);
        $fbIdArray = array();
        for ($i = 0; $i < $totalData; $i++) {
            $userWithoutBlock = $user->checkBlockList($fbID, $data[$i]);
            if (!empty($userWithoutBlock)) {
                array_push($fbIdArray, $data[$i]);
            }
        }
        $nonBlock = array_diff($data, $fbIdArray);
        $changeArray = implode(", ", $nonBlock);

        $userDetail = $user->getFullDetails($lat, $lng, '10', $changeArray);
        $msg = "Fetched successfully.";
        return $this->successMessageWithVar($msg, $userDetail);
    }
}
