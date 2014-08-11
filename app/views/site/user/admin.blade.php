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
      <div class="col-sm-10 user_name"><h1>@if(Cache::has(Auth::user()->id)) {{json_decode(Cache::get(Auth::user()->id))->name}} @else Name Not Available @endif</h1></div>
    </div>
    <div class="row">
      <div class="col-sm-3"><!--left col-->
              
          <div class="panel panel-default">
            <div class="panel-heading">Information <i class="fa fa-link fa-1x"></i></div>
            <div class="panel-body">Email:<br/><a href="/gov">@if(Cache::has(Auth::user()->id)) {{json_decode(Cache::get(Auth::user()->id))->email}} @else Not Available @endif</a></div>
            <div class="panel-body">Phone:<br/><a href="/gov">@if(Cache::has(Auth::user()->id)) {{json_decode(Cache::get(Auth::user()->id))->phone}} @else Not Available @endif</a></div>
            <div class="panel-body">Mobile:<br/><a href="/gov">@if(Cache::has(Auth::user()->id)) {{json_decode(Cache::get(Auth::user()->id))->mobile}} @else Not Available @endif</a></div>
            <div class="panel-body">Address:<br/><a href="/gov">@if(Cache::has(Auth::user()->id)) {{json_decode(Cache::get(Auth::user()->id))->street}} <br/> {{json_decode(Cache::get(Auth::user()->id))->city}}, {{json_decode(Cache::get(Auth::user()->id))->state}}, {{json_decode(Cache::get(Auth::user()->id))->zip}} @else Not Available @endif</a></div>
          </div>
          
        </div><!--/col-3-->
      <div class="col-sm-9">
            <ul class="nav nav-tabs" id="myTab">
            <li class="active"><a href="#doctors" data-toggle="tab">Doctors</a></li>
            <li ><a href="#patients" data-toggle="tab">Patients</a></li>
            <li ><a href="#admins" data-toggle="tab">Administrators</a></li>
          </ul>
              
          <div class="tab-content">
            <div class="tab-pane active" id="doctors">
             <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Patient Name</th>
                      <th>Patient Email</th>
                      <th>Phone</th>
                      <th>Address</th>
                      </tr>
                  </thead>
                  <tbody id="items">
                    @foreach($doctors as $user)
                    <tr>
                      <td>@if(Cache::has($user->id)) {{json_decode(Cache::get($user->id))->name}} @else Not Available @endif</td>
                      <td>@if(Cache::has($user->id)) {{json_decode(Cache::get($user->id))->email}} @else Not Available @endif</td>
                      <td>@if(Cache::has($user->id)) {{json_decode(Cache::get($user->id))->phone}} @else Not Available @endif</td>
                      <td>@if(Cache::has($user->id)) {{json_decode(Cache::get($user->id))->address}} @else Not Available @endif</td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>   
             </div><!--/tab-pane-->
             <div class="tab-pane" id="patients">
                
                  <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Patient Name</th>
                      <th>Patient Email</th>
                      <th>Phone</th>
                      <th>Address</th>
                      </tr>
                  </thead>
                  <tbody id="items">
                    @foreach($patients as $user)
                    <tr>      
                      <td>@if(Cache::has($user->id)) <a href="/patient/{{$user->id}}">{{json_decode(Cache::get($user->id))->name}}</a> @else Not Available @endif</td>
                      <td>@if(Cache::has($user->id)) {{json_decode(Cache::get($user->id))->email}} @else Not Available @endif</td>
                      <td>@if(Cache::has($user->id)) {{json_decode(Cache::get($user->id))->phone}} @else Not Available @endif</td>
                      <td>@if(Cache::has($user->id)) {{json_decode(Cache::get($user->id))->address}} @else Not Available @endif</td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>  
              </div>
              <div class="tab-pane" id="admins">
                
                  <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Patient Name</th>
                      <th>Patient Email</th>
                      <th>Phone</th>
                      <th>Address</th>
                      </tr>
                  </thead>
                  <tbody id="items">
                    @foreach($admins as $user)
                    <tr>
                      <td>@if(Cache::has($user->id)) {{json_decode(Cache::get($user->id))->name}} @else Not Available @endif</td>
                      <td>@if(Cache::has($user->id)) {{json_decode(Cache::get($user->id))->email}} @else Not Available @endif</td>
                      <td>@if(Cache::has($user->id)) {{json_decode(Cache::get($user->id))->phone}} @else Not Available @endif</td>
                      <td>@if(Cache::has($user->id)) {{json_decode(Cache::get($user->id))->address}} @else Not Available @endif</td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>  
              </div>
          </div><!--/tab-content-->
              </div>
@stop