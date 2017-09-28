<?php $site_id    = (isset($site)) ? $site->site_id : '' ?>
<?php $site_name  = (isset($site)) ? $site->site_name : null ?>
<?php $site_notes = (isset($site)) ? $site->description : null ?>

{!! Form::open(["id" => "site-form", "class" => "uk-form uk-form-horizontal", "enctype" => "multipart/form-data" ]) !!}
<div class="uk-form-row uk-margin-medium-top">
    {!! Form::label('site_sitename', 'Site Name', ['class' => 'uk-form-label']) !!}
    {!! Form::text('sites[site_name]',  $site_name, ['class' => 'uk-width-1-2', 'id' => 'site_sitename']) !!}
</div>
<div class="uk-form-row uk-margin-medium-top">
    {!! Form::label('site_description', 'Notes', ['class' => 'uk-form-label']) !!}
    {!! Form::textarea('sites[site_notes]', $site_notes, ['class' => 'uk-form-width-large']) !!}
</div>
<div class="uk-form-row uk-margin-medium-top">
    {!! Form::label('site_deffile', 'Definition File', ['class' => 'uk-form-label']) !!}
    {!! Form::file('site_deffile', ['id' => 'site_deffile']) !!}
</div>

<div class="uk-form-row uk-margin-medium-top">
    <label for="siteSave" class="uk-form-label"></label>
    <a id="toggle-{{ $button_id }}-input-template" name="siteSave" class="uk-button uk-button-primary uk-button-large" data-id="{{ $site_id }}" href="#">
        <i class="uk-icon-check-circle"> {{ $button_label }}</i>
    </a>
    <i class="loader uk-icon-spin"></i>
    <a name="siteSave" class="uk-button uk-button-primary uk-button-link" href="{{ URL::to('/templates') }}">Cancel</i>	</a>
    <br>
</div>

<meta name="_token" content="{{ app('Illuminate\Encryption\Encrypter')->encrypt(csrf_token()) }}" />
{!! Form::hidden('_method', 'POST') !!}

{!! Form::close() !!}