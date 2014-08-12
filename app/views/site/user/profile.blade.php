@extends('site.layouts.default')

{{-- Web site Title --}}
@section('title')
{{{ Lang::get('user/user.profile') }}} ::
@parent
@stop

{{-- Content --}}
@section('content')

@if(Cache::has($user->id))
<div class="page-header">
	<h1>User Profile</h1>
</div>

<table class="table table-striped">
    <thead>
    <tr>
        <th>Attribute</th>
        <th>Value</th>
    </tr>
    </thead>
    <tbody>
    @foreach(json_decode(Cache::get($user->id),true) as $name => $value)
        <tr>      
            <td>{{$name}}</td>
            <td>{{$value}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
@else
<div class="page-header">
    <h1>No Data Available</h1>
</div>
@endif
@stop
