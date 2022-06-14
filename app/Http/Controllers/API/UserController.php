<?php

namespace App\Http\Controllers\API;
use App\Http\Requests\API\SignUpUserApiRequest;
use App\Models\User;
use App\Models\User_details;
use App\Models\Appointment;
use App\Http\Controllers\AppBaseController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Models\Key_Token_Master;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\CommonController;
use Validator;
use DB;

class UserController extends AppBaseController
{
    public function index(Request $request)
    {
        $response = [];
        $response['success'] = FALSE;
    
            $users =  User::get();
            $data = $users;
            $respMsg = 'Data fatched successfully';
            // $response['success'] = TRUE;
            // $response['status'] = STATUS_OK;
      
            return $this->successResponse($data, '200',  $respMsg, "True");
    }
    public function signup(SignUpUserApiRequest $request)
    {
        $input = $request->all();
        
        if($input['password'] == $input['confirm_password']){

            $image =$_FILES['profile_image'];
            $tmp_name =$image["tmp_name"];
            $ext = pathinfo( $image['name'], PATHINFO_EXTENSION);
            $imageName = time().'.'.$ext;
            $imagePath = public_path(). '/uploads/images';
            move_uploaded_file($tmp_name,"$imagePath/$imageName");
            unset($input['confirm_password']);
            $input['password']=Hash::make($request['password']);
            $input['profile_image']=$imageName;

            $result = User::create($input);

            $respMsg = 'User Registered Successfully';
            $data = new UserResource($result);
            return AppBaseController::successResponse($data, '200', $respMsg, "True");
        }else{
            $respMsg = 'Password and confirm password both should be the same';
            return $this->responseError(404, $respMsg);
        }
    }
    public function login(Request $request)
    {
        $data = $request->all();
        $rules = [
            'email' => 'required|email',
            'password' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $respMsg = 'Please fill all details';
            return $this->responseError(404, $respMsg);
        }
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            $respMsg = 'These credentials do not match our records.';
            return $this->responseError(404, $respMsg);
        }else{
            $respMsg = 'Login Successfully';
            return $this->successResponse($data, '200',  $respMsg, "True");
        }        
    }

    public function getAllDoctors(Request $request)
    {
        $response = [];
        $response['success'] = FALSE;
            $users = User::where('user_type', 'psychologist')->get();
            $data = $users;
            $respMsg = 'Data fatched successfully';

            return $this->successResponse($data, '200',  $respMsg, "True");
    }
    
    public function getUser()
    {
        //errpor hoi to =>   return $this->responseError(0, $e->getMessage());

        return $this->successResponse($data, $respCode, $respMsg, "True");
    }

    public function register(Request $request)
    {
        try {
            extract($request->all());
            
            $devicetype = empty($_SERVER["HTTP_DEVICETYPE"]) ? "" : $_SERVER["HTTP_DEVICETYPE"];
            $versioncode = empty($_SERVER["HTTP_VERSIONCODE"]) ? "" : $_SERVER["HTTP_VERSIONCODE"];

            $valid = AppBaseController::requiredValidation([
                'first_name' => @$first_name,
                'last_name' => @$last_name,
                'email' => @$email,
                'phone' => @$phone,
                'gender' => @$gender,
                'country_code' => @$email,
                'password' => @$password,
                'language' => @$language,
            ]);
            if ($valid != '') {
                $msg = trans('words.please_enter') . $valid;
                return $this->responseError(0, $msg);
            }

            $image =$_FILES['profile_image'];

            $checkExistUser = User::where('email', $email)->first();
            if (!empty($checkExistUser)) {
                return AppBaseController::responseError(0, trans('words.already_register'));
            }
            if($password != $confirm_password){
                return AppBaseController::responseError(0, trans('words.password_and_confirm_password_does_not_match'));
            }

            if(!isset($device_token) || $device_token==""){
                $device_token="";
            }
            if (!isset($device_type) || $device_type == "") {
                $device_type = "";
            }
            
            $password=Hash::make($password);
            $created_at= date("Y/m/d");
            $updated_at= "";

            if (isset($image) && $image != '') {
                
                $tmp_name =$image["tmp_name"];
                $ext = pathinfo( $image['name'], PATHINFO_EXTENSION);
                $imageName = time().'.'.$ext;
                $imagePath = public_path(). '/uploads/images';
                move_uploaded_file($tmp_name,"$imagePath/$imageName");
                
            }else{
                $imageName='';
            }
            $is_confirm = 0;
            $respMsg = trans('words.confirmation_email');
            $respCode = 2;

            $insert = [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'phone' => $phone,
                'gender' => $gender,
                'country_code' => $country_code,
                'profile_image' => $imageName,
                'password' => $password,
                'user_type' => $user_type,
                'language' => $language,
                'is_confirm' => $is_confirm,
                'device_type' => $device_type,
                'device_token' => $device_token,
                'ucode' => "",

            ];
            $user_detail = User::create($insert);

            $data['data']=$user_detail;
            if($email!=''){
                // $sendMail = CommonController::verify_email($email, $fname . ' ' . $lname);
            }
            
            return AppBaseController::successResponse($data, $respCode, $respMsg, "True");
            
        } catch (\Exception $e) {
            return $this->responseError(0, $e->getMessage());
        }
    }
    public function signin(Request $request)
    {
        try {
            extract($request->all());
            $valid = AppBaseController::requiredValidation([
                'email' => @$email,
                'password' => @$password,
            ]);
            if ($valid != '') {
                $msg = trans('words.please_enter') . $valid;
                return $this->responseError(0, $msg);
            }

            $devicetype = empty($_SERVER["HTTP_DEVICETYPE"]) ? "" : $_SERVER["HTTP_DEVICETYPE"];
            $versioncode = empty($_SERVER["HTTP_VERSIONCODE"]) ? "" : $_SERVER["HTTP_VERSIONCODE"];

            if(!isset($device_token) || $device_token==""){
                $device_token="";
            }
            if (!isset($device_type) || $device_type == "") {
                $device_type = "";
            }

            $check_user = User::where('email', $email)->first();
            
            if(empty($check_user)){
                return AppBaseController::responseError(0, trans('words.incorrect_email'));
            }else{
                if(empty($check_user) || $check_user == null) {
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $errorLogin = trans('words.incorrect_email');
                        return AppBaseController::responseError(0, $errorLogin);
                    }
                } else {
                    if (!Hash::check($password, $check_user->password)) {
                        return AppBaseController::responseError(0, trans('words.incorrect_password'));
                    }

                    $updateObj = ['device_type' => $device_type, 'device_token' => $device_token];
                    $update = User::where('id',$check_user->id)->update($updateObj);

                    $user_detail = User::where('id',$check_user->id)->first();

                    $data['data'] = $user_detail;

                    return AppBaseController::successResponse($data, 1, trans('words.login_success'), "True");
                     
                }
            }
   
        } catch (\Exception $e) {
            return $this->responseError(0, $e->getMessage());
        }
    }
    public function adduserdetails(Request $request){
        try {
            extract($request->all());
            $valid = AppBaseController::requiredValidation([
                'user_id' => @$user_id,
                'language' => @$language,
                'country' => @$country,
                'age' => @$age,
            ]);
            if ($valid != '') {
                $msg = trans('words.please_enter') . $valid;
                return $this->responseError(0, $msg);
            }
                $insert = [
                    'user_id' => $user_id,
                    'language' => $language,
                    'country' => $country,
                    'age' => $age,
                    'therapy_type' => $therapy_type,
                    'gender' => $gender,
                    'relationship_status' => $relationship_status,
                    'identify_self' => $identify_self,
                    'financial_status' => $financial_status,
                    'sleeping_habits' => $sleeping_habits,
                    'is_religious' => $is_religious,
                    'want_special_session' => $want_special_session,
                    'therapy_taken' => $therapy_taken,
                    'expectations' => $expectations,
                    'is_medication' => $is_medication,
                    'thought_about_suicide' => $thought_about_suicide,
                    'is_feel_anxieties' => $is_feel_anxieties,
                    'therapy_consideration' => $therapy_consideration,
                    'therapist_preference' => $therapist_preference,
                    'other_detail' => $other_detail,
                    'source' => $source,
                ];
                $user_details = User_details::create($insert);
    
                $data['data']=$user_details;
    
                return AppBaseController::successResponse($data, 1,  trans('words.appointment_booked_successfuly'), "True");

        } catch (\Exception $e) {
            return $this->responseError(0, $e->getMessage());
        }
    }
    public function getBestTherapist(Request $request){
        // $checkExistUser = User::where('email', $email)->first();
    }
    public function bookAppointment(Request $request){
        try {
            extract($request->all());
            $valid = AppBaseController::requiredValidation([
                'user_id' => @$user_id,
                'dr_id' => @$dr_id,
                'date' => @$time,
                'time' => @$time,
                'payment_status' => @$payment_status,
                'total_payed' => @$total_payed,
                'payment_type' => @$payment_type,
            ]);

            if ($valid != '') {
                $msg = trans('words.please_enter') . $valid;
                return $this->responseError(0, $msg);
            }

                if($payment_status == "done"){
                    $insert = [
                        'user_id' => $user_id,
                        'dr_id' => $dr_id,
                        'date' => $date,
                        'time' => $time,
                        'payment_status' => $payment_status,
                        'total_payed' => $total_payed,
                        'payment_type' => $payment_type,
                    ];
                    $appointment = Appointment::create($insert);

                    $data['data']=$appointment;
        
                    return AppBaseController::successResponse($data, 1,  trans('words.appointment_booked_successfuly'), "True");
                }else{
                    return AppBaseController::responseError(0, trans('words.payment_is_not_done_yet'));
                }

        } catch (\Exception $e) {
            return $this->responseError(0, $e->getMessage());
        }
    }

    public function getAppointments(Request $request, $id){
        try {           
            
            $doctors =  DB::table('appointment')
                        ->select('users.*')
                        ->leftJoin('users','users.id','=','appointment.dr_id')
                        ->where('user_id','=', $id)
                        ->groupBy('dr_id')
                        ->get();

            $data['appointments'] =  Appointment::where('dr_id', $doctors[0]->id)->get();
            $data['doctor'] = $doctors;

            return $this->successResponse($data, '200', trans('words.data_fetched_successfully'), "True");

        } catch (\Exception $e) {
            return $this->responseError('appointment', $e->getMessage());
        }
    }
}
