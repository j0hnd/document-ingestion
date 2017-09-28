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
        <a href="{{ URL::to('/templates/add') }}" class="uk-button uk-button-large uk-float-right uk-button-primary"> <i class="uk-icon-plus-circle"> Add Input Template</i></a>

        <table class="uk-table uk-table-hover uk-table-striped">
            <thead>
            <tr>
                <th class="uk-width-1-4">Input Templates</th>
                <th class="uk-width-1-2"></th>
            </tr>
            </thead>
            <tbody id="sites-container">
            @include('sites::partials.lists', ['sites' => $sites])
            </tbody>
        </table>
        <meta name="_token" content="{{ app('Illuminate\Encryption\Encrypter')->encrypt(csrf_token()) }}" />
    </div>
@stop

@section('js')
    <script src="{{ asset('/libs/classes/sites.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            Sites.index();
        });
    </script>
@stop