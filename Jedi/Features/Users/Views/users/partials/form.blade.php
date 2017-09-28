<div id="create-user-container" class="uk-modal">
    <div class="uk-modal-dialog">
        <button type="button" class="uk-modal-close uk-close"></button>
        <div class="uk-modal-header">
            <h2>Add User</h2>
        </div>
        <span class="uk-icon-spin"></span>

        <ul class="uk-tab" data-uk-switcher="{connect: '#form', animation: 'fade'}">
            <li class="uk-active uk-width-1-3">
                <a href="">User Details</a>
            </li>

            <li class="uk-width-1-3">
                <a href="">User Types</a>
            </li>

            {{--<li class="uk-width-1-3">--}}
                {{--<a href="">Permissions</a>--}}
            {{--</li>--}}
        </ul>

        {!! Form::open(["id" => "create-user-form", "class" => "uk-panel uk-panel-box uk-form uk-form-stacked" ]) !!}
        <ul id="form" class="uk-switcher">
            <li>
                <a href="" data-uk-switcher-item="0"></a>
                <div id="form">
                    <div class="uk-margin-small-top">
                        {!! Form::label('firstname', 'Firstname', ['class' => 'uk-form-label']) !!}
                        {!! Form::text('user[firstname]',  null, ['class' => 'uk-width-1-1']) !!}
                    </div>

                    <div class="uk-margin-small-top">
                        {!! Form::label('lastname', 'Lastname', ['class' => 'uk-form-label']) !!}
                        {!! Form::text('user[lastname]',  null, ['class' => 'uk-width-1-1']) !!}
                    </div>

                    <div class="uk-margin-small-top">
                        {!! Form::label('email', 'Email', ['class' => 'uk-form-label']) !!}
                        {!! Form::email('user[email]',  null, ['class' => 'uk-width-1-1']) !!}
                    </div>

                    <div class="uk-margin-small-top">
                        {!! Form::label('password', 'Password', ['class' => 'uk-form-label']) !!}
                        <div class="uk-form-password">
                            <input type="password" name="user[password]" class="uk-width-1-1">
                            <a href="" class="uk-form-password-toggle" data-uk-form-password>...</a>
                        </div>
                    </div>

                    <div class="uk-margin-small-top uk-margin-medium-bottom">
                        {!! Form::label('confirm_password', 'Confirm Password', ['class' => 'uk-form-label']) !!}
                        <div class="uk-form-password">
                            <input type="password" name="user[confirm_password]" class="uk-width-1-1">
                            <a href="" class="uk-form-password-toggle" data-uk-form-password>...</a>
                        </div>
                    </div>
                </div>
            </li>

            <li>
                <a href="" data-uk-switcher-item="1"></a>
                @if($user_types['status'])
                    @foreach($user_types['data'] as $id => $type)
                        <div class="uk-margin-small-top">
                        {!! Form::radio('user[permissions]', $id,  ['class' => 'uk-margin-small-right']) !!}&nbsp;{{$type}}
                        </div>
                    @endforeach
                @else
                @endif
            </li>

            <li>
                <a href="" data-uk-switcher-item="2"></a>
            </li>
        </ul>

        <br/>

        <div class="uk-text-right">
            <button type="button" id="toggle-cancel-button" class="uk-button">Cancel</button>
            {!! Form::submit('Save', ['class' => 'uk-button uk-button-primary']) !!}
        </div>

        <meta name="_token" content="{{ app('Illuminate\Encryption\Encrypter')->encrypt(csrf_token()) }}" />
        {!! Form::hidden('_method', 'POST') !!}

        {!! Form::close() !!}
    </div>
</div>