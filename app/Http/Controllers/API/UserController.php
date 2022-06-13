<?php

namespace App\Http\Controllers\API;
use App\Http\Requests\API\SignUpUserApiRequest;
use App\Models\User;
use App\Http\Controllers\AppBaseController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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

}
