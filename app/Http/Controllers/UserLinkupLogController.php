<?php

namespace App\Http\Controllers;

use App\UserLinkupLog;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Validator;


class UserLinkupLogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $userLinkupLog = new UserLinkupLog();
        $inputData = Input::all();
        $userLastLinkup = $userLinkupLog->showLinkupLog($inputData);
        $msg = "Linkup list.";
        return $this->successMessageWithVar($msg, $userLastLinkup);
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
        $userLinkupLog = new UserLinkupLog();
        $inputData = Input::all();
        $validator = $this->checkValidation();
        if ($validator->fails()) {
            $messages = $validator->messages();
            foreach ($messages->all() as $message) {
                $msg[] = $message;
            }
            return $this->errorMessage($msg);
        }

        $userLinkupLog->logLinkupPersonalChat($inputData);
        $msg = "Linkup log saved successfully.";
        return $this->successMessage($msg);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    public function checkValidation()
    {
        return $validator = Validator::make(
            array(
                'sender_fb_id' => Input::get('sender_fb_id'),
                'receiver_fb_id' => Input::get('receiver_fb_id'),
                'message' => Input::get('message'),
            ), array(
            'sender_fb_id' => 'required',
            'receiver_fb_id' => 'required',
            'message' => 'required'
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
}
