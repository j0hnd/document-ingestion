@extends('layouts.default')

@section('title')
    @parent
    {{ $title }}
@stop

@section('css')
@stop

@section('content')
    <div class="uk-vertical-align uk-text-center uk-height-1-1">
        <div class="uk-vertical-align-middle" style="width: 250px;">
            <img class="uk-margin-bottom" width="140" height="120" src="{{ asset('img/nologo.jpg') }}" alt="Company Logo">
            <div id="login-alert" name="login-alert" class="uk-alert uk-alert-danger uk-hidden" data-uk-alert>
                <a href="" class="uk-alert-close uk-close"></a>
                <p>Login Unsuccessful</p>
            </div>
            <p class="uk-text-info">Forgot Password</p>
            {!! Form::open(["id" => "forgot-password-form", "class" => "uk-panel uk-panel-box uk-form", "url" => URL::to('/authenticate/forgot-password')  ]) !!}
            <div class="uk-form-row">
                {!! Form::email('_email', null, ['class' => 'uk-width-1-1 uk-form-large', 'placeholder' => 'Email Address']) !!}
            </div>
            <div class="uk-form-row">
                {!! Form::submit('Submit', ['id' => 'submit-forgot-password', 'class' => 'uk-width-1-1 uk-button uk-button-primary uk-button-large']) !!}
            </div>
            <div class="uk-form-row uk-text-small">
                <a class="uk-float-right uk-link uk-link-muted" href="{{URL::to('/sign-in')}}">Back to Log in</a>
            </div>
            <meta name="_token" content="{{ app('Illuminate\Encryption\Encrypter')->encrypt(csrf_token()) }}" />
            {!! Form::hidden('_method', 'POST') !!}
            {!! Form::close() !!}
        </div>
    </div>
@stop

@section('js')
    <script src="{{ asset('/libs/classes/login.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            Login.ForgotPassword();
        });
    </script>
@stop