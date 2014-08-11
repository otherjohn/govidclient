<?php

class PatientController extends BaseController {

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
        list($user,$redirect) = $this->user->checkAuthAndRedirect('doctor');
        if($redirect){return $redirect;}

        // Show the page
        return View::make('site/doctor/index', compact('doctor'));
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

        return View::make('site/doctor/profile', compact('doctor'));
    }


    /**
     * Get user's profile
     * @param $username
     * @return mixed
     */
    public function getList()
    {
        
        return View::make('site/doctors', compact('doctors'));
    }



    public function getLogin(){
        $user = Auth::user();
        if(!empty($user->id)){
            return Redirect::to('/doctor');
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
                return Redirect::to('/doctor');
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
     * Log the user out of the application.
     *
     */
    public function getLogout()
    {
        Auth::logout();
        return Redirect::to('/');
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
