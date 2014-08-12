@extends('site.layouts.default')

{{-- Content --}}
@section('content')
      <!-- Main component for a primary marketing message or call to action -->
      <div class="jumbotron">
        <h1>Welcome to Health.gov </h1>
        <p>This App is used to demonstrate how OpenID Connect can help government agencies<br/>
        	perform their duties while keeping identities safe.</p>

        <p>This App requires that you be registered at <a href="http://gov.nellcorp.com" target="_blank">http://gov.nellcorp.com</a><br/><br/>
        When you click on the login button below, you will be redirected there to authorize this app to access your information.<br/><br/>
        After your aproval, we will have access to your data for a limited time, after which, you'll need to login again.
        </p>
        <p>
          <a class="btn btn-lg btn-primary" href="{{{ URL::to('user/login') }}}" role="button">Login &raquo;</a>
        </p>
      </div>
@stop
