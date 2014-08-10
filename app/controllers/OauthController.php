<?php
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OauthController extends BaseController {

	 /**
     * The Auth Server Object
     * @var Authorization
     */
    protected $request;
    protected $data;

	//In your controller constuctor you should instantiate the auth server:
    public function __construct(){
        $this->request = new League\OAuth2\Server\Util\Request($_GET);
        $this->data = new Client(Config::get('app.provider'), Config::get('app.client_id'), Config::get('app.client_secret'));
    }


public function action_code(){

    try {
        
        $this->data->authenticate();
        $data = $this->data->requestUserInfo();
        
        if(empty($data)){
            throw new Exception("Unable to retrieve user info");
        }
        
        //See if user already exists
        if(empty(User::where('pid',$data['sub'])->first()->id)){
            $user = new User();
            $user->pid = $data['sub'];
        }else{
            $user = User::where('pid',$data['sub'])->first();
        }
            $user->access_token = $this->data->getAccessToken();
            $this->user->save();

        if ( $user->id ){

            Cache::put($user->id,json_encode($data), 720); //Cache user data for 12 hours
            
            //Attach Roles to user
            $user->saveRoles(array($data['role']));

            //login user
            Auth::login($user);

            //redirect user to their profile
            return Redirect::to('/user');
        }else{
            // Get validation errors (see Ardent package)
            $error = $this->user->errors()->all();

            return Redirect::to('user/create')->with( 'error', $error );
        }

    } catch (League\OAuth2\Server\Exception\ClientException $e) {

        // Throw an exception because there was a problem with the client's request
        $response = array(
            'error' =>  $e->getCode(),
            'error_description' => $e->getMessage()
        );

        // Set the correct header
        header($this->data->getExceptionHttpHeaders($e->getCode())[0]);
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

}