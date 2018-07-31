<?php

namespace App\Http\Controllers;

use App\AccessToken;
use App\Company;
use App\Http\Service\AuthService;
use App\User;
use App\Vip;
use Config;
use Validator;
use Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /** @var AuthService  */
    protected $authService;

    public function __construct()
    {
        $this->authService = \App::make('AuthService');
    }

    public function register(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'username' => 'required|string|max:255',
                'email' => 'required|email|unique:users|max:255',
                'password' => 'required|max:255',
                'phone_number' => 'string|unique:users|max:100',
            ]
        );

        if($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()
            ],400);
        }

        $user = new User();
        $user->fill($request->input());
        $user->vip_id = Vip::NORMAL;
        $user->avatar = $user->getDefaultAvatar();
        $user->role = 'user';
        $user->save();


        return response()->json(Config::get('constant.success.register'),201);
    }

    public function login(Request $request)
    {
        $email = $request->email;
        $password = $request->password;

        $user = User::where('email',$email)->first();
        if(empty($user)) {
            return Config::get('constant.error.email_not_exists');
        }

        if(Hash::check($password,$user->password) == false) {
            return Config::get('constant.error.password_wrong');
        }


        if($request->header('login-site') == 'admin' && $user->role != 'admin') {
            return Config::get('constant.error.role');
        }

        //check IP
        $ip = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $accessToken = AccessToken::where('ip', $ip)
            ->where('user_agent', $user_agent)
            ->where('user_id', $user->id)->first();
        if(empty($accessToken)) {
            //create access token
            $accessToken = new AccessToken([
                'token' => md5(uniqid($user->id, true)),
                'user_id' => $user->id,
                'user_agent' => $user_agent,
                'expire_time' => date('Y-m-d H:i:s',strtotime('+ 4hours')),
                'ip' => $ip
            ]);
            try{
                $accessToken->save();
            } catch (\Exception $e) {
                \Log::error($e->getMessage());
                return Config::get('constant.error.db');
            }
            //end here
        }

        if($accessToken->isExpire()) {
            $accessToken->refreshToken();
        }

        return response()->json([
            'status' => 1,
            'user'   => $user,
            'token'  => $accessToken->token
        ],200);
    }

    public function logout()
    {
        $accessToken = \Request::get('token');
        $accessToken->delete();

        return Config::get('constant.success.logout');
    }

    public function refreshToken(Request $request)
    {
        if($request->header('api-key') != 'praisethesun') {
            return response()->json(Config::get('constant.error.role'),401);
        }
        $token = $request->header('app-token');
        $codeRefresh = $request->header('code-refresh');

        $accessToken = AccessToken::where('token',$token)
            ->where('code_refresh',$codeRefresh)
            ->first();

        if(empty($accessToken)) {
            return [
                'status' => 0,
                'message' => 'Không thể làm mới token hãy thử lại sau'
            ];
        }
        $accessToken->refreshToken();

        return [
            'status' => 1,
            'message' => 'Làm mới token thành công',
        ];
    }

    public function anonymousLogin()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $accessToken = new AccessToken([
            'token' => md5(uniqid(microtime(), true)),
            'user_agent' => $user_agent,
            'anonymous' => true,
            'expire_time' => date('Y-m-d H:i:s',strtotime('+ 12hours')),
            'ip' => $ip
        ]);

        $accessToken->save();

        $accessToken->name = 'Anonymous'.$accessToken->id;
        $accessToken->save();

        return [
            'statsus' => 1,
            'username' => $accessToken->name,
            'token' => $accessToken->token,
            'role' => 'anonymous'
        ];
    }
}
