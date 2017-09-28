@extends('layouts.default')

@section('title')
    @parent
    {{ $title }}
@stop

@section('css')
@stop

@section('content')
    @include('layouts.partials.menu', ['page' => 'queue'])
@stop

@section('js')

@stop