@extends('layouts.default')

@section('title')
    @parent
    {{ $title }}
@stop

@section('content')
<form id="frm">
	<label>{{ $title }}</label>
	<input type="text" name="name" />

	<input type="button" id="submit" value="save" />
</form>
<input type="hidden" id="token" name="_token" value="{{ csrf_token() }}" />
@stop

@section('js')
	<script src="{{ asset('/libs/classes/test.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            Test.form();
        });
    </script>
@stop