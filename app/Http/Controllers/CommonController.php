<?php

namespace App\Http\Controllers;

use App\Models\Key_Master;
use App\Models\Key_Token_Master;
use App\Models\User_Master;
use App\Models\Version_Master;
use Faker\Provider\Image;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\CommonController;
use Prophecy\Exception\Exception;
use Storage;

class CommonController extends Controller
{

    public static function backgroundPostLocal($url)
    {
        ignore_user_abort(true);
        ini_set('max_execution_time', 0);
        set_time_limit(0);
        $parts    = parse_url($url);
        $fp     = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 30);
        if (!$fp) {
            return false;
        } else {
            if (!isset($parts['query'])) {
                $query = '';
            } else {
                $query = $parts['query'];
            }
            $out = "GET " . $parts['path'] . " HTTP/1.1\r\n";
            $out .= "Host: " . $parts['host'] . "\r\n";
            $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $out .= "Content-Length: " . strlen($query) . "\r\n";
            $out .= "Connection: Close\r\n\r\n";
            $out .= $query;
            fwrite($fp, $out);
            fclose($fp);
            return true;
        }
    }

    public static function updateFirsbaseUserDtl($user_id)
    {
        $userDtl = User_Master::where(['user_id' => $user_id])->first();
        
        $url = env('FIREBASE_URL');
       
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url . "CHATUSERLIST.json");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
       
        $jres = json_decode($result,true); 
        curl_close($ch);
      
        $data = [];
        if($jres != null && count($jres) > 0){
            foreach ($jres as $key => $value) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url . "CHATUSERLIST/" . $key . '.json');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $result = curl_exec($ch);
                $getUser = json_decode($result,true);
                curl_close($ch);
                if($getUser != null && count($getUser) > 0){
                    foreach ($getUser as $k => $val) {
                        $getUserKey = '';
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url . "CHATUSERLIST/" . $key . '/' . $k . "/.json");
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        $result = curl_exec($ch);
                        $getUserKey = json_decode($result);
                        curl_close($ch);
                        if ($getUserKey != '' && $getUserKey->user_id == $user_id) {
                            $fields = array(
                                "device_token" => $userDtl->device_token,
                                "device_type" => $userDtl->device_type,
                                "profile" => $userDtl->profile,
                                "username" => $userDtl->name,
                            );
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $url . "CHATUSERLIST/" . $key . '/' . $k . ".json");
                            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                            $headers = array();
                            $headers[] = "Content-Type: application/x-www-form-urlencoded";
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                            $result = curl_exec($ch);
        
                            curl_close($ch);
                        }
                    }
                }
            }
        }
        
        return 1;
    }

    public static function testMail($email)
    {

        $message_body = 'test mail text';
        $server_name = env('APP_NAME');
        $template = 'send_mail';
        $subject = 'Test';
        $data = array('template' => $template, 'email' => $email, 'subject' => $subject, 'message_body' => $message_body, 'serverName' => $server_name);

        Mail::send($data['template'], $data, function ($message) use ($data) {
            $message->from(env('MAIL_USERNAME'), $data['serverName']);
            $message->to($data['email'])->subject($data['subject']);
        });
        return true;
    }
    public static function verify_email($email, $name)
    {
        $email1 = base64_encode($email);
        $link = env('APP_URL') . "/mail?email=$email1";
        $email_header = env('APP_URL') . "/public/images/ehead.png";
        $email_footer = env('APP_URL') . "/public/images/envelope.png";
        $server_name = env('APP_NAME');

        $message_body = '<div style="margin:0;padding:0;font-family:Lato,Tahoma,Verdana,Segoe,sans-serif;font-size:14px">
          <table cellspacing="0" cellpadding="0" width="100%" bgcolor="#EEEEEE" style="vertical-align:top;border-collapse:collapse">
          <tbody>
                <tr style="vertical-align:top;border-collapse:collapse">
                    <td align="center" valign="top" style="vertical-align:top;border-collapse:collapse">

                        <div style="min-width:320px;max-width:600px;width:100%;margin:0 auto">

                            <div style="padding:0">
                                <a href="#">
                                    <img align="center" border="0" src="' . $email_header . '" alt="" title="" style="max-width:525px;width:87.5%;margin:10px auto 0" class="CToWUd">
                                </a>
                            </div>
                            <div style="background:#fff;overflow:hidden;padding:0;max-width:525px;width:87.5%;text-align:left">
                            <div style="padding:0 15px;margin-bottom:15px">
                                <div style="font-size:18px;margin:0 0 5px;display:block;color:#000;text-decoration:none;text-align:center;">
                                    <b>Dear ' . $name . '!</b><br/><br/>
                                    Thank you creating an account with ' . $server_name . '.<br><br>
                                    To access your account we need you to finalize the verification process. <br><br>
                                    Please <a href="' . $link . '">click here</a> to confirm your email.<br>
                                </div>
                            </div>
                            <div style="color:#000;display:block;margin:10px 0;font-size:15px;text-align:center;text-decoration:none">
                                Sincerely yours,<br/>
                                Team ' . $server_name . '</div>
                            </div>

                            <div style="max-width:600px;margin-bottom:10px"><img src="' . $email_footer . '" alt="" style="max-width:100%" class="CToWUd a6T" tabindex="0"><div class="a6S" dir="ltr" style="opacity: 0.01; left: 1032px; top: 1949.25px;"><div id=":27g" class="T-I J-J5-Ji aQv T-I-ax7 L3 a5q" role="button" tabindex="0" data-tooltip-class="a1V"><div class="aSK J-J5-Ji aYr"></div></div></div></div>
                        </div>

                    </td>
              </tr>
          </tbody>
      </table>

      </div>';

        $template = 'send_mail';
        $subject = 'Important: Please verify your email address';
        $data = array('template' => $template, 'email' => $email, 'subject' => $subject, 'message_body' => $message_body, 'serverName' => $server_name);

        Mail::send($data['template'], $data, function ($message) use ($data) {
            $message->from(env('MAIL_USERNAME'), $data['serverName']);
            $message->to($data['email'])->subject($data['subject']);
        });
        return true;
    }

    public static function mailForForgotPassword($email, $name)
    {

        $encEmail = base64_encode($email);
        $email_header = env('APP_URL') . "/public/images/ehead.png";
        $email_footer = env('APP_URL') . "/public/images/envelope.png";
        $server_name = env('APP_NAME');
        $link = env('APP_URL') . "/resetPwd?email=$encEmail";
        $logo = env('APP_URL') . "/public/images/logo.png";

        $messageForgotPass = '<div style="margin:0;padding:0;font-family:Lato,Tahoma,Verdana,Segoe,sans-serif;font-size:14px">
        <table cellspacing="0" cellpadding="0" width="100%" bgcolor="#EEEEEE" style="vertical-align:top;border-collapse:collapse">
        <tbody>
            <tr style="vertical-align:top;border-collapse:collapse">
                <td align="center" valign="top" style="vertical-align:top;border-collapse:collapse">
                    <div style="min-width:320px;max-width:600px;width:100%;margin:0 auto">
                        <div style="padding:0">
                            <a href="#">
                                <img align="center" border="0" src="' . $email_header . '" alt="" title="" style="max-width:525px;width:87.5%;margin:10px auto 0" class="CToWUd">
                            </a>
                        </div>
                        <div style="background:#fff;overflow:hidden;padding:0;max-width:525px;width:87.5%;text-align:left">
                                    <div style="padding:0 15px;margin-bottom:15px">
                                        <div style="font-size:18px;margin:0 0 5px;display:block;color:#000;text-decoration:none;text-align:center;">
                                        <b>Hi ' . $name . '!</b><br/><br/>
                                        We\'ve received a request to reset your password. If you didn\'t make the request, just ignore this email.<br/>
                                            <a href="' . $link . '"> Click here to change your password.</a><br/><br/>
                                            If you have any questions or trouble logging on please contact an app administrator.
                                        </div>
                                    </div>
                            <div style="color:#000;display:block;margin:10px 0;font-size:15px;text-align:center;text-decoration:none">
                            Sincerely yours,<br/>
                                  ' . $server_name . ' Team</div>
                        </div>
                        <div style="max-width:600px;margin-bottom:10px"><img src="' . $email_footer . '" alt="" style="max-width:100%" class="CToWUd a6T" tabindex="0"><div class="a6S" dir="ltr" style="opacity: 0.01; left: 1032px; top: 1949.25px;"><div id=":27g" class="T-I J-J5-Ji aQv T-I-ax7 L3 a5q" role="button" tabindex="0" data-tooltip-class="a1V"><div class="aSK J-J5-Ji aYr"></div></div></div></div>
                    </div>
                </td>
            </tr>
        </tbody>
        </table>
        </div>';
        $template = 'send_mail';
        $subject = 'Forgot Password Mail';
        $data = array('template' => $template, 'email' => $email, 'subject' => $subject, 'message_body' => $messageForgotPass, 'serverName' => $server_name);

        Mail::send($data['template'], $data, function ($message) use ($data) {
            $message->from(env('MAIL_USERNAME'), $data['serverName']);
            $message->to($data['email'])->subject($data['subject']);
        });

        return true;
    }

    public static function generateRandomCode()
    {
        $alphabet = 'AbCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return strtoupper(implode($pass));
    }
    public static function RandomStringGenerator()
    {
        $n = 32;
        $generated_string = "";
        $domain = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
        $len = strlen($domain);
        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, $len - 1);
            $generated_string = $generated_string . $domain[$index];
        }
        return $generated_string;
    }
    static public function checkmode($mode)
    {
        try {

            $response = ($mode != 1) ? 1 : 0;
        } catch (Exception $e) {
            exit;
        }
        return $response;
    }
    public static function requiredValidation($collection)
    {
        foreach ($collection as $key => $value) {
            if (empty($value) || $value == null) {
                return $key;
            } else {
            }
        }
    }

    static public function checkKeyExist($key)
    {
        try {
            $result = Key_Master::where("key_name", $key)->count();
            $response = ($result != 0) ? 1 : 0;
        } catch (Exception $e) {
            exit;
        }
        return $response;
    }

    static public function checkKeyTokenExist($key, $token)
    {
        try {
            $result = Key_Token_Master::where("key", $key)->where("token", $token)->count();
            $response = ($result != 0) ? 1 : 0;
        } catch (Exception $e) {
            exit;
        }
        return $response;
    }

    public static function resizePicture($img, $file_name, $path, $new_path, $new_width, $new_height)
    {

        $image = Image::make($img);
        $image->save($path . $file_name);
        $image->resize($new_width, $new_height)->save($new_path . $file_name);


        /*$img = Image::make($img);
        $img->resize($new_width, $new_height);
        $img->insert($path);
        $img->save($new_path);*/
    }

    public static function mailContactUs($name, $subject, $message)
    {
        $server_name = env('APP_NAME');
        $logo = env('PUBLIC_PATH') . "images/logo.png";

        $message_body = '<div dir="ltr"><div class="adM"><br><br>
             </div><div class=""><center>
              <table cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="font-size:14px;background-color:#f0f0f0">
             <tbody><tr>
              <td style="padding:10px;padding-bottom:0px;">
               <table cellspacing="0" cellpadding="0" border="0" width="100%">
              <tbody><tr>
               <td  style="background: #ffeb5c;">
                <table cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width:600px">

               <tbody>
               <tr>
                    <td align="center" style="padding:20px"><a target="_blank" style="outline:none;border:0px">
                    <img border="0" align="absbottom" alt="" src="' . $logo . '" class="CToWUd" width="90" height="90"></a></td>
               </tr>
               <tr>
                <td style="padding:20px;text-align:center;font-size:22px;background-color:#fff;border-top-right-radius:7px;border-top-left-radius:7px"></td>
               </tr>
                </tbody></table> </td>
              </tr>
               </tbody></table> </td>
             </tr>
             <tr>
              <td>
               <table cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width:600px;padding:20px;padding-top:5px;text-align:center;line-height:22px;font-size:16px;background-color:#fff;border-bottom-right-radius:7px;border-bottom-left-radius:7px">
              <tbody>
              <tr>
               <td colspan="2" style="padding-top:10px;font-family:Georgia, Times New Roman; font-size:21px;"><b>User Information:</b></td>
              </tr>

              <tr>
               <td style="padding-top:10px;font-family:Georgia, Times New Roman;">Name : <b>' . $name . '</b></td>
              </tr>

              <tr>
               <td style="padding-top:10px;font-family:Georgia, Times New Roman;">Subject : <b>' . $subject . '<b/></td>
              </tr>

              <tr>
               <td style="padding-top:10px;font-family:Georgia, Times New Roman;">Message  :  <b>' . $message . '</b></td>
              </tr>

              <tr><td style="line-height: 19px;font-family:Georgia, Times New Roman;"><p>Sincerely yours,<br />
              ' .  $server_name . ' Team</p></td></tr>
               </tbody></table> </td>
             </tr>
           <tr>
            <td style="padding:10px;text-align:center;font-size:10px;color:#898989;"> </td>
           </tr>
           <tr>
            <td style="padding:5px;text-align:center;font-size:11px;color:#000;">&copy; ' . date("Y") . ', All Rights Reserved | ' . $server_name . '</td>
           </tr>
              </tbody></table>
             </center>
             </div></div>';

        $template = 'send_mail';
        $subject = 'Contact us from ' . $server_name;

        $data = array('template' => $template, 'email' => env('CLIENT_EMAIL'), 'subject' => $subject, 'message_body' => $message_body, 'serverName' => $server_name);

        Mail::send($data['template'], $data, function ($message) use ($data) {
            $message->from(env('MAIL_USERNAME'), $data['serverName']);
            $message->to($data['email'])->subject($data['subject']);
        });
        return true;
    }



    public static function backgroundPost($url)
    {

        ignore_user_abort(true);
        ini_set('max_execution_time', 0);
        set_time_limit(0);
        $parts    = parse_url($url);

        // $fp     = fsockopen($parts['host'],80, $errno, $errstr, 30);

        $fp = fsockopen('ssl://' . $parts['host'], 443, $errno, $errstr, 30);
        // $fp = fsockopen('http://localhost:8080/API/APImyCountrymyWord', 80, $errno, $errstr, 30);
        if (!$fp) {
            return false;
        } else {
            if (!isset($parts['query'])) {
                $query = '';
            } else {
                $query = $parts['query'];
            }
            $out = "POST " . $parts['path'] . " HTTP/1.1\r\n";
            $out .= "Host: " . $parts['host'] . "\r\n";
            $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $out .= "Content-Length: " . strlen($query) . "\r\n";
            $out .= "Connection: Close\r\n\r\n";
            $out .= $query;
            fwrite($fp, $out);
            fclose($fp);
            return true;
        }
    }

    public static function versionCheck($deviceType = "android", $versioncode)
    {
        $getVersion = Version_Master::where(["device_type" => $deviceType])->first();
        $Result = false;
        if (!empty($getVersion) && $versioncode < $getVersion->version_code) {
            $Result = true;
        }
        return $Result;
    }
}