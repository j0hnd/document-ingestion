@extends('layouts.default')

@section('title')
    @parent
    {{ $title }}
@stop

@section('css')
@stop

@section('content')
    @include('layouts.partials.menu', ['page' => ''])

    <div id="main" class="uk-container uk-container-center">

        <h1>Input Template</h1>
        @include('sites::partials.site-form', ['button_label' => 'Save', 'button_id' => 'save'])

    </div>
@stop

@section('js')
    <script src="{{ asset('/libs/classes/sites.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            Sites.add();
        });
    </script>
@stop


