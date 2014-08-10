<?php

class AdminClientsController extends AdminController {


    /**
     * Client Model
     * @var Client
     */
    protected $client;

    /**
     * Inject the models.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        parent::__construct();
        $this->client = $client;
    }


public function keygen($length=40)
{
    $key = '';
    list($usec, $sec) = explode(' ', microtime());
    mt_srand((float) $sec + ((float) $usec * 100000));
    
    $inputs = array_merge(range('z','a'),range(0,9),range('A','Z'));

    for($i=0; $i<$length; $i++)
    {
        $key .= $inputs{mt_rand(0,61)};
    }
    return $key;
}

    /**
     * Show a list of all the blog posts.
     *
     * @return View
     */
    public function getIndex()
    {
        // Title
        $title = Lang::get('admin/clients/title.blog_management');

        // Grab all the blog posts
        $clients = $this->client;

        // Show the page
        return View::make('admin/clients/index', compact('clients', 'title'));
    }

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function getCreate()
	{
        // Title
        $title = Lang::get('admin/clients/title.create_a_new_blog');

        // Show the page
        return View::make('admin/clients/create_edit', compact('title'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function postCreate()
	{
        // Declare the rules for the form validation
        $rules = array(
            'name'   => 'required|min:3',
            'description' => 'required|min:3',
            'email'   => 'required|min:3',
            'callback'   => 'required|min:3',
            'website'   => 'required|min:3'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            // Create a new blog post
            $user = Auth::user();

            // Update the blog post data
            $this->client->name            = Input::get('name');
            $this->client->id            = $this->keygen();
            $this->client->secret            = $this->keygen();
            
            //$this->client->user_id          = $user->id;

            
            // Was the blog post created?
            if($this->client->save()){
                
                //Create Endpoint Object
                $endpoint = new ClientEndpoint(array('redirect_uri' => Input::get('callback')));
                
                $metadata = array(
                    new ClientMetadata(array('key' => 'slug', 'value' => Str::slug(Input::get('name')))),
                    new ClientMetadata(array('key' => 'description', 'value' => Str::slug(Input::get('description')))),
                    new ClientMetadata(array('key' => 'email', 'value' => Str::slug(Input::get('email')))),
                    new ClientMetadata(array('key' => 'website', 'value' => Str::slug(Input::get('website'))))
                );

                
                if(! $this->client->endpoint()->save($endpoint)){

                    //delete client
                    $id = $this->client->id;
                    $this->client->delete();

                    // Was the blog post deleted?
                    $client = Client::find($id);
                
                    if(empty($client)){
                        // Redirect to the blog post create page
                        return Redirect::to('admin/clients/create')->with('error', Lang::get('admin/clients/messages.create.endpoint_error'));
                    }                    
                        
                }

                if(! $this->client->metadata()->saveMany($metadata)){

                    //delete client
                    $id = $this->client->id;
                    $this->client->delete();

                    // Was the blog post deleted?
                    $client = Client::find($id);
                
                    if(empty($client)){
                        // Redirect to the blog post create page
                        return Redirect::to('admin/clients/create')->with('error', Lang::get('admin/clients/messages.create.metadata_error'));
                    }                    
                        
                }
                
                // Redirect to the new blog post page
                return Redirect::to('admin/clients/' . $this->client->id . '/edit')->with('success', Lang::get('admin/clients/messages.create.success'));
                
            }

            // Redirect to the blog post create page
            return Redirect::to('admin/clients/create')->with('error', Lang::get('admin/clients/messages.create.error'));
        }

        // Form validation failed
        return Redirect::to('admin/clients/create')->withInput()->withErrors($validator);
	}

    /**
     * Display the specified resource.
     *
     * @param $client
     * @return Response
     */
	public function getShow($client)
	{
        // redirect to the frontend
        return Redirect::to($client->url());
	}

    /**
     * Show the form for editing the specified resource.
     *
     * @param $client
     * @return Response
     */
	public function getEdit($client)
	{
        // Title
        $title = Lang::get('admin/clients/title.blog_update');

        // Show the page
        return View::make('admin/clients/create_edit', compact('client', 'title'));
	}

    /**
     * Update the specified resource in storage.
     *
     * @param $client
     * @return Response
     */
	public function postEdit($client)
	{

        // Declare the rules for the form validation
$rules = array(
            'name'   => 'required|min:3',
            'description' => 'required|min:3',
            'email'   => 'required|min:3',
            'callback'   => 'required|min:3',
            'website'   => 'required|min:3'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);
        //var_dump($validator->passes());die();
        // Check if the form validates with success
        if ($validator->passes())
        {
            // Update the blog post data
            $client->name            = Input::get('name');
            $client->slug             = Str::slug(Input::get('name'));
            $client->description          = Input::get('description');
            $client->email      = Input::get('email');
            $client->website = Input::get('website');
            $client->endpoint()->redirect_uri = Input::get('callback');
            if($client->endpoint()->save()){

                // Was the blog post updated?
                if($client->save()){
                
                    // Redirect to the new blog post page
                    return Redirect::to('admin/clients/' . $client->id . '/edit')->with('success', Lang::get('admin/clients/messages.update.success'));
                }

                // Redirect to the blogs post management page
                return Redirect::to('admin/clients/' . $client->id . '/edit')->with('error', Lang::get('admin/clients/messages.update.endpoint_error'));    
            }

            
            // Redirect to the blogs post management page
            return Redirect::to('admin/clients/' . $client->id . '/edit')->with('error', Lang::get('admin/clients/messages.update.error'));

            
        }

        // Form validation failed
        return Redirect::to('admin/clients/' . $client->id . '/edit')->withInput()->withErrors($validator);
	}


    /**
     * Remove the specified resource from storage.
     *
     * @param $client
     * @return Response
     */
    public function getDelete($client)
    {
        // Title
        $title = Lang::get('admin/clients/title.blog_delete');

        // Show the page
        return View::make('admin/clients/delete', compact('client', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $client
     * @return Response
     */
    public function postDelete($client)
    {
        // Declare the rules for the form validation
        $rules = array(
            'id' => 'required|integer'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            $id = $client->id;

             //this should also delete related models that have the mysql "on delete cascade" attribute set
            $client->delete();

            // Was the blog post deleted?
            $client = Client::find($id);
            if(empty($client))
            {
                // Redirect to the blog posts management page
                return Redirect::to('admin/clients')->with('success', Lang::get('admin/clients/messages.delete.success'));
            }
        }
        // There was a problem deleting the blog post
        return Redirect::to('admin/clients')->with('error', Lang::get('admin/clients/messages.delete.error'));
    }

    /**
     * Show a list of all the blog posts formatted for Datatables.
     *
     * @return Datatables JSON
     */
    public function getData()
    {
        $clients = Client::select(array('oauth_clients.id', 'oauth_clients.name', 'oauth_clients.created_at'));

        return Datatables::of($clients)

        
        ->add_column('actions', '<a href="{{{ URL::to(\'admin/clients/\' . $id . \'/edit\' ) }}}" class="btn btn-default btn-xs iframe" >{{{ Lang::get(\'button.edit\') }}}</a>
                <a href="{{{ URL::to(\'admin/clients/\' . $id . \'/delete\' ) }}}" class="btn btn-xs btn-danger iframe">{{{ Lang::get(\'button.delete\') }}}</a>
            ')

        ->remove_column('id')

        ->make();
    }

}