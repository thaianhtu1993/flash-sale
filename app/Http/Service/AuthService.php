<?php
namespace App\Http\Service;

use App\AccessToken;
use App\CallClick;
use App\Title;
use App\Comment;
use App\Like;
use App\User;

class AuthService
{
    /**
     * @return bool
     */
    public function isProductLogin() {
        if($this->getUser()->role == 'product') {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isAnonymousLogin()
    {
        if(\Request::get('token')->anonymous) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isLogin()
    {
        if(\Request::get('token')) {
            return true;
        }

        return false;
    }

    /** @return User */
    public function getUser()
    {
        if($this->isLogin() && !$this->isAnonymousLogin()) {
            return \Request::get('token')->user;
        }

        return null;
    }

    /**
     * @return AccessToken
     */
    public function getAccessToken()
    {
        return \Request::get('token');
    }

    public function checkPromoteTitle(User $user)
    {
        $commentCount = Comment::where('user_id', $user->id)
            ->count();
        $likeCount = Like::where('user_id', $user->id)
            ->count();
        $callCount = CallClick::where('user_id', $user->id)
            ->count();

        foreach($this->getTitleConfig() as $config) {
            if($commentCount < $config->comment_count || $likeCount < $config->like_count || $callCount < $config->callCount) break;
            //if count satisfy promote user
            if($commentCount >= $config->comment_count && $likeCount >= $config->like_count && $callCount >= $config->call_count) {
                $user->title_id = $config->id;
            }
        }
        $user->save();

        return true;
    }

    private function getTitleConfig()
    {
        $titles = Title::orderBy('comment_count','ASC')
            ->get();

        return $titles;
    }

}


