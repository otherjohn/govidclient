<?php

class UserController extends BaseController {

    /**
     * User Model
     * @var User
     */
    protected $user;

    /**
     * Inject the models.
     * @param User $user
     */
    public function __construct(User $user)
    {
        parent::__construct();
        $this->user = $user;
    }

    /**
     * Users settings page
     *
     * @return View
     */
    public function getIndex()
    {
        list($user,$redirect) = $this->user->checkAuthAndRedirect('user');
        if($redirect){return $redirect;}

        // Show the page
        return View::make('site/user/index', compact('user'));
    }


    public function getLogin(){
        $user = Auth::user();
        if(!empty($user->id)){
            return Redirect::to('/user');
        }

    try {
        
        $request = new League\OAuth2\Server\Util\Request($_GET);
        $client = new Client(Config::get('app.provider'), Config::get('app.client_id'), Config::get('app.client_secret'), Config::get('app.url'));
        
        $req = $request->get();


        if(array_key_exists('code', $req) and array_key_exists('state', $req)){
            Session::put('oauth_code',$req['code']);
            Session::put('oauth_state',$req['state']);
        }else{
            Session::forget('oauth_nonce');
            Session::forget('oauth_state');
        }


        $client->addScope(array('openid','profile'));
        $client->authenticate();

        $data = $client->requestUserInfo();
        
        //dd($data);

        if(empty($data)){
            throw new Exception("Unable to retrieve user info");
        }
        
        //See if user already exists
        $password = Hash::make($data['sub']);
        $user = User::where('pid',$password)->first();
        
        if(empty($user)){
            $user = new User();
            $user->pid = $password;
        }

            $user->access_token = $client->getAccessToken();

            $user->save();
            //dd($user->id);
        if ( $user->id ){

            Cache::put($user->id,json_encode($data), 1440); //Cache user data for 24 hours
            
            //Attach Roles to user

            $user->saveRoles(array($data['role']));
            //dd('here');
            //login user
            if (Auth::attempt(array('id' => $user->id, 'password' => $data['sub']))){

                //redirect user to their profile
                return Redirect::to('/user');
            }
            //Auth::login($user);
            
        }else{
            
            return Redirect::to('user/login')->with( 'error', "An error occurred when loggin in. Please contact your ID provider." );
        }

    } catch (League\OAuth2\Server\Exception\ClientException $e) {

        // Throw an exception because there was a problem with the client's request
        $response = array(
            'error' =>  $e->getCode(),
            'error_description' => $e->getMessage()
        );

        // Set the correct header
        header($data->getExceptionHttpHeaders($e->getCode())[0]);
        header('Content-type: application/json');
        echo json_encode($response);

    } catch (Exception $e) {

        // Throw an error when a non-library specific exception has been thrown
        $response = array(
            'error' =>  'undefined_error',
            'error_description' => $e->getMessage()
        );
        header('Content-type: application/json');
        echo json_encode($response);
    }
}

    /**
     * Edits a user
     *
     */
    public function postEdit($user)
    {

        // Validate the inputs
        $validator = Validator::make(Input::all(), $user->getUpdateRules());
        //var_dump($user->getUpdateRules());die();
        

        if ($validator->passes())
        {
            $oldUser = clone $user;
            //$user->username = Input::get( 'username' );
            $user->email = Input::get( 'email' );
            $user->first_name = Input::get( 'first_name' );
            $user->last_name = Input::get( 'last_name' );
            $user->street = Input::get( 'street' );
            $user->city = Input::get( 'city' );
            $user->state = Input::get( 'state' );
            $user->zip = Input::get( 'zip' );
            $user->phone = Input::get( 'phone' );
            $user->mobile = Input::get( 'mobile' );
    

            $password = Input::get( 'password' );
            $passwordConfirmation = Input::get( 'password_confirmation' );

            if(!empty($password)) {
                if($password === $passwordConfirmation) {
                    $user->password = $password;
                    // The password confirmation will be removed from model
                    // before saving. This field will be used in Ardent's
                    // auto validation.
                    $user->password_confirmation = $passwordConfirmation;
                } else {
                    // Redirect to the new user page
                    return Redirect::to('users')->with('error', Lang::get('admin/users/messages.password_does_not_match'));
                }
            } else {
                unset($user->password);
                unset($user->password_confirmation);
            }

            $user->prepareRules($oldUser, $user);

            // Save if valid. Password field will be hashed before save
            $user->amend();
        }

        // Get validation errors (see Ardent package)
        $error = $user->errors()->all();

        //var_dump($error);die();
        
        if(empty($error)) {
            return Redirect::to('user')
                ->with( 'success', Lang::get('user/user.user_account_updated') );
        } else {
            return Redirect::to('user')
                ->withInput(Input::except('password','password_confirmation'))
                ->with( 'error', $error );
        }
    }


    
    /**
     * Log the user out of the application.
     *
     */
    public function getLogout()
    {
        Auth::logout();
        return Redirect::to('/');
    }

    /**
     * Get user's profile
     * @param $username
     * @return mixed
     */
    public function getProfile($username)
    {
        $userModel = new User;
        $user = $userModel->getUserByUsername($username);

        // Check if the user exists
        if (is_null($user))
        {
            return App::abort(404);
        }

        return View::make('site/user/profile', compact('user'));
    }

    public function getSettings()
    {
        list($user,$redirect) = User::checkAuthAndRedirect('user/settings');
        if($redirect){return $redirect;}

        return View::make('site/user/profile', compact('user'));
    }

    /**
     * Process a dumb redirect.
     * @param $url1
     * @param $url2
     * @param $url3
     * @return string
     */
    public function processRedirect($url1,$url2,$url3)
    {
        $redirect = '';
        if( ! empty( $url1 ) )
        {
            $redirect = $url1;
            $redirect .= (empty($url2)? '' : '/' . $url2);
            $redirect .= (empty($url3)? '' : '/' . $url3);
        }
        return $redirect;
    }
}
