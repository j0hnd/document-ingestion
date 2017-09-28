@extends('layouts.default')

@section('title')
    @parent
    {{ $title }}
@stop

@section('css')
    <link href="{{ asset('/css/components/tooltip.min.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/components/form-password.min.css') }}" rel="stylesheet">
@stop

@section('content')
    @include('layouts.partials.menu', ['page' => ''])
    <div id="main" class="uk-container uk-container-center">
        <a href="#create-user-container" class="uk-button uk-button-large uk-float-right uk-button-primary" data-uk-modal=""> <i class="uk-icon-plus-circle"> Create User</i></a>

        <table class="uk-table uk-table-hover uk-table-striped">
            <thead>
            <tr>
                <th class="uk-width-1-3">{!! Form::checkbox('check-all', 1, null, ['class' => 'uk-margin-small-right', 'id' => 'check-all-users']) !!}Name</th>
                <th class="uk-width-1-4">Type</th>
                <th class="uk-width-1-2">Sites</th>
            </tr>
            </thead>
            <tbody>
            @include('users::users.partials.lists', ['users' => $users])
            </tbody>
        </table>
    </div>

    {{-- create user modal form --}}
    @include('users::users.partials.form', ['user_types' => $user_types])
@stop

@section('js')
<script src="{{ asset('/libs/components/jquery/src/tooltip.min.js') }}"></script>
<script src="{{ asset('/libs/classes/users.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function () {
        var $create_user_modal = UIkit.modal('#create-user-container');
        $('body').on('click', '#toggle-cancel-button', function () {
            $create_user_modal.hide();
        });

        Users.indexUser();
        Users.createUser();
    });
</script>
@stop