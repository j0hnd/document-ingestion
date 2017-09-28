<div id="add-edit-site" class="uk-modal">
</div>

<div id="change-password" class="uk-modal">
    <div class="uk-modal-dialog">
        <button type="button" class="uk-modal-close uk-close"></button>
        <div class="uk-modal-header">
            <h2>Change Password</h2>
        </div>

        <form id="reset-password-form" class="uk-form uk-form-horizontal">
            <fieldset data-uk-margin>
                <div class="uk-form-row uk-form-password">
                    {!! Form::label('password', 'Password', ['class' => 'uk-form-label']) !!}
                    <div class="uk-form-password">
                        <input type="password" name="password" class="uk-width-1-1">
                        <a href="" class="uk-form-password-toggle" data-uk-form-password>...</a>
                    </div>
                </div>
                <div class="uk-form-row uk-form-password">
                    {!! Form::label('password', 'Confirm Password', ['class' => 'uk-form-label']) !!}
                    <div class="uk-form-password">
                        <input type="password" name="confirm_password" class="uk-width-1-1">
                        <a href="" class="uk-form-password-toggle" data-uk-form-password>...</a>
                    </div>
                </div>
            </fieldset>
        </form>

        <div class="uk-modal-footer uk-text-right">
            <button type="button" class="uk-button uk-modal-close">Cancel</button>
            <button type="button" class="uk-button uk-button-primary uk-modal-close" id="toggle-user-reset-pwd">Reset</button>
        </div>
    </div>
</div>

<!-- content will be parsed automatically using json request -->
<div id="edit-site" class="uk-modal"></div>