<?php

use Zizaco\Entrust\HasRole;
use Carbon\Carbon;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class User extends Eloquent implements UserInterface, RemindableInterface{

    use HasRole;


        /**
         * Validation rules
         */
    public function info()
    {
        
        if(Cache::has($this->id)){
            return Cache::get($this->id);
        }else{
            if(!empty($this->access_token)){
                //get results from server
               $client = new Client(Config::get('app.provider'), Config::get('app.client_id'), Config::get('app.client_secret'), Config::get('app.url'));
               $client->setAccessToken($this->access_token);
               $data = $client->requestUserInfo();
               if(empty($data)){
                    throw new Exception("Unable to retrieve user info");
               }
               Cache::put($this->id,json_encode($data), Config::get('app.user_cache')); //Cache user data for 24 hours
               return $data;
            }

            return false;
        }

        
    }


    public function isDoctor(){
        return $this->hasRole('doctor');
    }

    public function isAdmin(){
        return $this->hasRole('admin');
    }

    public function isPatient(){
        return $this->hasRole('patient');
    }

    public function data(){
        return $this->hasMany('UserData', 'user_id','id');
    }



    /**
     * Get the date the user was created.
     *
     * @return string
     */
    public function joined()
    {
        return String::date(Carbon::createFromFormat('Y-n-j G:i:s', $this->created_at));
    }

    /**
     * Save roles inputted from multiselect
     * @param $inputRoles
     */
    public function saveRoles($inputRoles)
    {
        $roles = array();
        foreach ($inputRoles as $name) {
            $role = DB::table('roles')->where('name', $name)->first();
            if(empty($role)){continue;}
            $roles[] = $role->id;
        }

        if(! empty($roles)) {
            $this->roles()->sync($roles);
        } else {
            $this->roles()->detach();
        }
    }

    /**
     * Returns user's current role ids only.
     * @return array|bool
     */
    public function currentRoleIds()
    {
        $roles = $this->roles;
        $roleIds = false;
        if( !empty( $roles ) ) {
            $roleIds = array();
            foreach( $roles as &$role )
            {
                $roleIds[] = $role->id;
            }
        }
        return $roleIds;
    }

    /**
     * Redirect after auth.
     * If ifValid is set to true it will redirect a logged in user.
     * @param $redirect
     * @param bool $ifValid
     * @return mixed
     */
    public static function checkAuthAndRedirect($redirect, $ifValid=false)
    {
        // Get the user information
        $user = Auth::user();
        $redirectTo = false;

        if(empty($user->id) && ! $ifValid) // Not logged in redirect, set session.
        {
            Session::put('loginRedirect', $redirect);
            $redirectTo = Redirect::to('user/login')
                ->with( 'notice', Lang::get('user/user.login_first') );
        }
        elseif(!empty($user->id) && $ifValid) // Valid user, we want to redirect.
        {
            $redirectTo = Redirect::to($redirect);
        }

        return array($user, $redirectTo);
    }

    public function currentUser()
    {
        return Auth::user();
    }

    /**
     * Get the e-mail address where password reminders are sent.
     *
     * @return string
     */
    public function getReminderEmail()
    {
        return $this->email;
    }



    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier(){
        return $this->id;
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword(){
        return $this->pid;
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken()
    {
        return $this->remember_token;
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string  $value
     * @return void
     */
    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return 'remember_token';
    }

}
