@extends('layouts.default')

@section('title')
    @parent
    {{ $title }}
@stop

@section('css')
    <link href="{{ asset('/css/components/form-password.min.css') }}" rel="stylesheet">
@stop

@section('content')
    @include('layouts.partials.menu', ['page' => ''])

    <div id="main" class="uk-container uk-container-center">
        <h1>User: {{ $user->firstname }}&nbsp;{{ $user->lastname }}</h1>
        <form class="uk-form uk-form-horizontal">
            <fieldset data-uk-margin>
                <div class="uk-form-row">
                    <label for="user_admin" class="uk-form-label">Is Administrator?</label>
                    <input type="checkbox" name="user_admin" id="user_admin" class="" {{ ($user->is_admin) ? 'checked' : ''  }}>
                </div>

                <table class="uk-table uk-table-hover uk-table-striped">
                    <thead>
                    <tr>
                        <th>Site</th>
                        <th>Permissions</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                        @include('users::users.partials.site-permissions')
                    </tbody>
                </table>

                <div class="uk-form-row">
                    <a id="user_save" name="user_save" class="uk-button uk-button-primary uk-button-large" href="#"><i class="uk-icon-check-circle"> Save</i>	</a>
                    <a id="user_resetpw" name="user_resetpw" class="uk-button uk-button-large" href="#change-password" data-id="{{ $user->user_id }}" data-uk-modal><i class="uk-icon-cog"> Reset Password</i>	</a>
                    <a id="user_disable" name="user_disable" class="uk-button uk-button-large uk-button-danger" href="#" data-id="{{ $user->user_id }}"><i class="uk-icon-times-circle"> Disable</i></a>

                </div>
            </fieldset>
            <meta name="_token" content="{{ app('Illuminate\Encryption\Encrypter')->encrypt(csrf_token()) }}" />
        </form>
        @include('users::users.partials.modals')
    </div>
    <input type="hidden" value="{{$user->user_id}}" name="user_id" />
@stop

@section('js')
    <script src="{{ asset('/libs/classes/users.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            Users.updateUser();

//            var $create_user_modal = UIkit.modal('#create-user-container');
//            $('body').on('click', '#toggle-cancel-button', function () {
//                $create_user_modal.hide();
//            });
//
//            Users.indexUser();
//            Users.createUser()
//
            Users.saveUserSites();
            Users.updateUserSites();
            Users.deleteUserSite();
        });
    </script>
@stop