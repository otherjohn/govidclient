@extends('site.layouts.default')

{{-- Content --}}
@section('content')
      <!-- Main component for a primary marketing message or call to action -->
      <div class="jumbotron">
        <h1>Gov ID </h1>
        <p>This site will be used to design the health provider application.</p>
        <p>
          <a class="btn btn-lg btn-primary" href="{{{ URL::to('user/login') }}}" role="button">Login &raquo;</a>
        </p>
      </div>
@stop
