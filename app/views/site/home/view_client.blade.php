@extends('site.layouts.default')
{{-- Web site Title --}}
@section('title')
{{{ String::title($client->name) }}} ::
@parent
@stop

{{-- Content --}}
@section('content')
<h3>{{ $client->name }}</h3>

<p>{{ $client->content() }}</p>

@stop