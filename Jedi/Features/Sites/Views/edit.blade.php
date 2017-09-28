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

        <h1>Edit: Input Template</h1>
        @include('sites::partials.site-form', ['site' => $site, 'button_label' => 'Update', 'button_id' => 'update'])

    </div>
@stop

@section('js')
    <script src="{{ asset('/libs/classes/sites.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            Sites.update();
        });
    </script>
@stop


