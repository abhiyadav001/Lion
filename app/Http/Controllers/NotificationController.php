<?php

namespace App\Http\Controllers;

use App\DeviceDetails;
use App\Notification;
use Illuminate\Http\Request;
use Input;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class NotificationController extends Controller
{

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
                    'id' => $userUpdated)
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
        //
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
     * @param  Request $request
     * @return Response
     */
    public function store()
    {
        $notification = new Notification();
        $deviceDetail = new DeviceDetails();
        $data = Input::all();
        $reciverToken = $deviceDetail->getLatestDetailByUserId($data['reciver_id']);
        $sendNotification = $notification->sendPushNotification($data, $reciverToken);
        $msg = "Successfully.";
        return $this->successMessageWithVar($msg, $sendNotification);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        //
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
     * @param  Request $request
     * @param  int $id
     * @return Response
     */
    public function update($id)
    {
        $notification = new Notification();
        $deviceDetail = new DeviceDetails();
        $data = Input::all();
        $reciverToken = $deviceDetail->getLatestDetailByUserId($data['reciver_id']);
        $sendNotification = $notification->sendConfNotification($data, $reciverToken, $id);
        $msg = "Your location is send successfully.";
        return $this->successMessageWithVar($msg, $sendNotification);
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

}
