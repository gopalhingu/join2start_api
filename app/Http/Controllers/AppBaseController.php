<?php

namespace App\Http\Controllers;

use InfyOm\Generator\Utils\ResponseUtil;
use Response;

/**
 * @SWG\Swagger(
 *   basePath="/api/v1",
 *   @SWG\Info(
 *     title="Laravel Generator APIs",
 *     version="1.0.0",
 *   )
 * )
 * This class should be parent class for other API controllers
 * Class AppBaseController
 */
class AppBaseController extends Controller
{
    public function sendResponse($result, $message)
    {
        return Response::json(ResponseUtil::makeResponse($message, $result));
    }

    public function sendError($error, $code = 404)
    {
        return Response::json(ResponseUtil::makeError($error), $code);
    }

    public static function responseError($response, $message)
    {
        return Response::json(array('ResponseCode' => $response, 'ResponseMsg' => $message, 'Result' => "False","ServerTime"=>date('T')));
    }

    public static function responseSuccess($response, $message, $result)
    {
        return Response::json(array('ResponseCode' => $response, 'ResponseMsg' => $message, 'Result' => $result,"ServerTime"=>date('T')));
    }
    
    public static function successResponse($array, $response, $message, $result)
    {
        $array['ResponseCode'] = $response; 
        $array['ResponseMsg'] = $message;
        $array['Result'] = $result;
        $array['ServerTime'] = date('T');

        return Response::json($array);
    }

    public static function requiredValidation($collection)
    {
        foreach ($collection as $key => $value) {
            
            if (empty($value) || $value == null) {
                return $key;
            }
        }
    }
    public static function RandomStringGenerator($n)
    {

        $generated_string = "";
        $domain = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
        $len = strlen($domain);
        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, $len - 1);
            $generated_string = $generated_string . $domain[$index];
        }
        return $generated_string;
    }
    public function sendSuccess($message)
    {
        return Response::json([
            'success' => true,
            'message' => $message
        ], 200);
    }

    //push-notification
    public static function sendPush_Admin($msg, $token,$noti_type)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $arrayToSend = array(
            "to" => $token,
            'data' => array(
                "badge" => 1,
                "title" => env("APP_NAME"),
                "message" => $msg,
                "text" => $msg,
                "body" => $msg,
                "noti_type" => $noti_type,
                "is_background" => false,
                "timestamp" => date('Y-m-d G:i:s'),
                "sound" => "default",
            ),
            "priority" => 'high',
            'notification' => array(
                "badge" => 1,
                "title" => env("APP_NAME"),
                "message" => $msg,
                "text" => $msg,
                "body" => $msg,
                "noti_type" => $noti_type,
                "is_background" => false,
                "timestamp" => date('Y-m-d G:i:s'),
                "sound" => "default",
            )
        );
        $json = json_encode($arrayToSend);
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: key='.env('push_notification_key');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

     public static function sendPush($msg, $token)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $arrayToSend = array("to" => $token,
            'data' => array(
                "badge" => 1,
                "type" => 2,
                "title" => env("APP_NAME"),
                "message" => $msg,
                "text" => $msg,
                "body" => $msg,
                "is_background" => false,
                "timestamp" => date('Y-m-d G:i:s'),
                "sound" => "default",
            ),
            "priority" => 'high',
            'notification' => array(
                "badge" => 1,
                "type" => 2,
                "title" => env("APP_NAME"),
                "message" => $msg,
                "text" => $msg,
                "body" => $msg,
                "is_background" => false,
                "timestamp" => date('Y-m-d G:i:s'),
                "sound" => "default",
            )
        );
        $json = json_encode($arrayToSend);

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: key='.env('push_notification_key');
 
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
