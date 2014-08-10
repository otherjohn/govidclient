@extends('site.layouts.default')

{{-- Web site Title --}}
@section('title')
{{{ Lang::get('user/user.settings') }}} ::
@parent
@stop

{{-- New Laravel 4 Feature in use --}}
@section('styles')
@parent
body {
  background: #f2f2f2;
}
@stop

{{-- Content --}}
@section('content')
  <div class="row">
      <div class="col-sm-10 user_name"><h1>Joseph Doe</h1></div>
    </div>
    <div class="row">
      <div class="col-sm-3"><!--left col-->
              
          <ul class="list-group">
            <li class="list-group-item text-muted">Profile</li>
            <li class="list-group-item text-right"><span class="pull-left"><strong>Username</strong></span> {{{$user->username}}}</li>
            <li class="list-group-item text-right"><span class="pull-left"><strong>Joined</strong></span> {{{$user->joined()}}}</li>
          </ul> 
               
          <div class="panel panel-default">
            <div class="panel-heading">Information <i class="fa fa-link fa-1x"></i></div>
            <div class="panel-body">Email:<br/><a href="/gov">user@mymail.com</a></div>
            <div class="panel-body">Phone:<br/><a href="/gov">555.555.5555</a></div>
            <div class="panel-body">Mobile:<br/><a href="/gov">555.555.5555</a></div>
            <div class="panel-body">Address:<br/><a href="/gov">1 Lom Memorial Drive<br/>Rochester, NY, 14623</a></div>
          </div>
          
        </div><!--/col-3-->
      <div class="col-sm-9">
        @if (Auth::check())
        @if (Auth::user()->hasRole('admin'))
        <div class="tab-pane" id="settings">
                <form class="form" action="{{ URL::to('user/' . $user->id . '/edit') }}" method="post" id="registrationForm">
                    <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                      <div class="form-group">
                          
                          <div class="col-xs-6">
                              <label for="first_name"><h4>First name</h4></label>
                              <input type="text" class="form-control" name="first_name" id="first_name" placeholder="first name" value="{{{ Input::old('first_name', $user->first_name) }}}">
                              {{ $errors->first('first_name', '<span class="help-inline">:message</span>') }}
                          </div>
                      </div>
                      <div class="form-group">
                          
                          <div class="col-xs-6">
                            <label for="last_name"><h4>Last name</h4></label>
                              <input type="text" class="form-control" name="last_name" id="last_name" placeholder="last name" value="{{{ Input::old('last_name', $user->last_name) }}}">
                              {{ $errors->first('last_name', '<span class="help-inline">:message</span>') }}
                          </div>
                      </div>
          
                      <div class="form-group">
                          
                          <div class="col-xs-6">
                              <label for="phone"><h4>Phone</h4></label>
                              <input type="text" class="form-control" name="phone" id="phone" placeholder="enter phone" value="{{{ Input::old('phone', $user->phone) }}}">
                              {{ $errors->first('phone', '<span class="help-inline">:message</span>') }}
                          </div>
                      </div>
                      <div class="form-group {{{ $errors->has('email') ? 'error' : '' }}}">
                          
                          <div class="col-xs-6">
                              <label for="email"><h4>Email</h4></label>
                              <input type="email" class="form-control" name="email" id="email" placeholder="you@email.com" value="{{{ Input::old('email', $user->email) }}}" title="enter your email.">
                              {{ $errors->first('email', '<span class="help-inline">:message</span>') }}
                          </div>
                      </div>
                      <div class="form-group {{{ $errors->has('password') ? 'error' : '' }}}">
                          
                          <div class="col-xs-6">
                              <label for="password"><h4>Password</h4></label>
                              <input type="password" class="form-control" name="password" id="password" placeholder="password" title="enter your password.">
                              {{ $errors->first('password', '<span class="help-inline">:message</span>') }}
                          </div>
                      </div>
                      <div class="form-group {{{ $errors->has('password_confirmation') ? 'error' : '' }}}">
                          
                          <div class="col-xs-6">
                            <label for="password2"><h4>Verify</h4></label>
                              <input type="password" class="form-control" name="password_confirmation" id="password2" placeholder="password2" title="enter your password2.">
                              {{ $errors->first('password_confirmation', '<span class="help-inline">:message</span>') }}
                          </div>
                      </div>
                      <div class="form-group">
                          <div class="col-xs-6">
                              <label for="steet"><h4>Street</h4></label>
                              <input type="text" name="street" class="form-control" id="location" placeholder="somewhere" value="{{{ Input::old('street', $user->street) }}}">
                              {{ $errors->first('street', '<span class="help-inline">:message</span>') }}
                          </div>
                      </div>
                      <div class="form-group">
                          <div class="col-xs-6">
                              <label for="city"><h4>City</h4></label>
                              <input type="text" name="city" class="form-control" id="location" placeholder="somewhere" value="{{{ Input::old('city', $user->city) }}}">
                              {{ $errors->first('city', '<span class="help-inline">:message</span>') }}
                          </div>
                      </div>
                      <div class="form-group">
                          <div class="col-xs-6">
                              <label for="steet"><h4>State</h4></label>
                              <input type="text" name="state" class="form-control" id="location" placeholder="somewhere" value="{{{ Input::old('state', $user->state) }}}">
                              {{ $errors->first('state', '<span class="help-inline">:message</span>') }}
                          </div>
                      </div>
                      <div class="form-group">
                          <div class="col-xs-6">
                              <label for="steet"><h4>Zip</h4></label>
                              <input type="text" name="zip" class="form-control" id="location" placeholder="somewhere" value="{{{ Input::old('zip', $user->zip) }}}">
                              {{ $errors->first('zip', '<span class="help-inline">:message</span>') }}
                          </div>
                      </div>
                      <div class="form-group">
                           <div class="col-xs-12">
                                <br>
                                <button class="btn btn-lg btn-success" type="submit"><i class="glyphicon glyphicon-ok-sign"></i> Save</button>
                            </div>
                      </div>
                </form>
              </div>
        @else
          <ul class="nav nav-tabs" id="myTab">
            <li><a href="#settings" data-toggle="tab">Profile</a></li>
            <li class="active"><a href="#home" data-toggle="tab">Approved Apps</a></li>
          </ul>
              
          <div class="tab-content">
            <div class="tab-pane active" id="home">
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Label 1</th>
                      <th>Label 2</th>
                      <th>Label 3</th>
                      <th>Label </th>
                      <th>Label </th>
                      <th>Label </th>
                    </tr>
                  </thead>
                  <tbody id="items">
                    <tr>
                      <td>1</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                    </tr>
                    <tr>
                      <td>2</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                    </tr>
                    <tr>
                      <td>3</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                    </tr>
                    <tr>
                      <td>4</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                    </tr>
                    <tr>
                      <td>5</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                    </tr>
                    <tr>
                      <td>6</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                    </tr>
                    <tr>
                      <td>7</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                    </tr>
                     <tr>
                      <td>8</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                    </tr>
                    <tr>
                      <td>9</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                    </tr>
                    <tr>
                      <td>10</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                      <td>Table cell</td>
                    </tr>
                  </tbody>
                </table>
                <hr>
                <div class="row">
                  <div class="col-md-4 col-md-offset-4 text-center">
                    <ul class="pagination" id="myPager"></ul>
                  </div>
                </div>
              </div><!--/table-resp-->
              
             </div><!--/tab-pane-->
             <div class="tab-pane" id="settings">
                
                  <hr>
                  <form class="form" action="{{ URL::to('user/' . $user->id . '/edit') }}" method="post" id="registrationForm">
                    <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                      <div class="form-group">
                          
                          <div class="col-xs-6">
                              <label for="first_name"><h4>First name</h4></label>
                              <input type="text" class="form-control" name="first_name" id="first_name" placeholder="first name" value="{{{ Input::old('first_name', $user->first_name) }}}">
                              {{ $errors->first('first_name', '<span class="help-inline">:message</span>') }}
                          </div>
                      </div>
                      <div class="form-group">
                          
                          <div class="col-xs-6">
                            <label for="last_name"><h4>Last name</h4></label>
                              <input type="text" class="form-control" name="last_name" id="last_name" placeholder="last name" value="{{{ Input::old('last_name', $user->last_name) }}}">
                              {{ $errors->first('last_name', '<span class="help-inline">:message</span>') }}
                          </div>
                      </div>
          
                      <div class="form-group">
                          
                          <div class="col-xs-6">
                              <label for="phone"><h4>Phone</h4></label>
                              <input type="text" class="form-control" name="phone" id="phone" placeholder="enter phone" value="{{{ Input::old('phone', $user->phone) }}}">
                              {{ $errors->first('phone', '<span class="help-inline">:message</span>') }}
                          </div>
                      </div>
                      <div class="form-group {{{ $errors->has('email') ? 'error' : '' }}}">
                          
                          <div class="col-xs-6">
                              <label for="email"><h4>Email</h4></label>
                              <input type="email" class="form-control" name="email" id="email" placeholder="you@email.com" value="{{{ Input::old('email', $user->email) }}}" title="enter your email.">
                              {{ $errors->first('email', '<span class="help-inline">:message</span>') }}
                          </div>
                      </div>
                      <div class="form-group {{{ $errors->has('password') ? 'error' : '' }}}">
                          
                          <div class="col-xs-6">
                              <label for="password"><h4>Password</h4></label>
                              <input type="password" class="form-control" name="password" id="password" placeholder="password" title="enter your password.">
                              {{ $errors->first('password', '<span class="help-inline">:message</span>') }}
                          </div>
                      </div>
                      <div class="form-group {{{ $errors->has('password_confirmation') ? 'error' : '' }}}">
                          
                          <div class="col-xs-6">
                            <label for="password2"><h4>Verify</h4></label>
                              <input type="password" class="form-control" name="password_confirmation" id="password2" placeholder="password2" title="enter your password2.">
                              {{ $errors->first('password_confirmation', '<span class="help-inline">:message</span>') }}
                          </div>
                      </div>
                      <div class="form-group">
                          <div class="col-xs-6">
                              <label for="steet"><h4>Street</h4></label>
                              <input type="text" name="street" class="form-control" id="location" placeholder="somewhere" value="{{{ Input::old('street', $user->street) }}}">
                              {{ $errors->first('street', '<span class="help-inline">:message</span>') }}
                          </div>
                      </div>
                      <div class="form-group">
                          <div class="col-xs-6">
                              <label for="city"><h4>City</h4></label>
                              <input type="text" name="city" class="form-control" id="location" placeholder="somewhere" value="{{{ Input::old('city', $user->city) }}}">
                              {{ $errors->first('city', '<span class="help-inline">:message</span>') }}
                          </div>
                      </div>
                      <div class="form-group">
                          <div class="col-xs-6">
                              <label for="steet"><h4>State</h4></label>
                              <input type="text" name="state" class="form-control" id="location" placeholder="somewhere" value="{{{ Input::old('state', $user->state) }}}">
                              {{ $errors->first('state', '<span class="help-inline">:message</span>') }}
                          </div>
                      </div>
                      <div class="form-group">
                          <div class="col-xs-6">
                              <label for="steet"><h4>Zip</h4></label>
                              <input type="text" name="zip" class="form-control" id="location" placeholder="somewhere" value="{{{ Input::old('zip', $user->zip) }}}">
                              {{ $errors->first('zip', '<span class="help-inline">:message</span>') }}
                          </div>
                      </div>
                      <div class="form-group">
                           <div class="col-xs-12">
                                <br>
                                <button class="btn btn-lg btn-success" type="submit"><i class="glyphicon glyphicon-ok-sign"></i> Save</button>
                            </div>
                      </div>
                </form>
              </div>
          </div><!--/tab-content-->
          @endif
          @endif
        </div><!--/col-9-->
@stop