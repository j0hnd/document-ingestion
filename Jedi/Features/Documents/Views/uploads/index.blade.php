@extends('layouts.default')

@section('title')
    @parent
    {{ $title }}
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/components/upload.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/placeholder.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/form-file.min.css') }}">
@stop

@section('content')
@include('layouts.partials.menu', ['page' => 'uploads'])

<div id="main" class="uk-container uk-container-center">
    <h2>Load Documents</h2>
    <div class="uk-grid">

        <div class="uk-width-6-10">
            <div id="upload-drop" class="uk-placeholder uk-text-center">
                <i class="uk-icon-cloud-upload uk-icon-medium uk-text-muted uk-margin-small-right uk-margin-large-top uk-margin-large-bottom"></i> Attach documents by dropping them here or <a class="uk-form-file">selecting them<input id="upload-select" type="file" multiple></a>.
            </div>
            <div id="progressbar" class="uk-progress uk-hidden">
                <div class="uk-progress-bar" style="width: 0%;">0%</div>
            </div>
            <div class="uk-clearfix">&nbsp;</div>
            <div class="filesInfo"></div>
            <div class="filesCount uk-text-bold"></div>
        </div>

        <div class="uk-width-4-10">
            {!! Form::open(["id" => "upload-form", "class" => "uk-panel uk-panel-box uk-form uk-form-stacked" ]) !!}
            <fieldset>
                <div class="uk-form-row">
                    <div class="uk-form-select" data-uk-form-select>
                        {!! Form::label('input_processor', 'Input Processor', ['class' => 'uk-form-label']) !!}
                        {!! Form::select('input_processor', $sites, null, ['class' => 'uk-width-1-1']) !!}
                    </div>
                </div>
                <div class="uk-form-row">
                    <div class="uk-form-select" data-uk-form-select>
                        {!! Form::label('destination', 'Output Destination', ['class' => 'uk-form-label']) !!}
                        {!! Form::select('destination', $output_destinations, '-1', ['id' => 'destination', 'class' => 'uk-width-1-1']) !!}
                    </div>
                </div>
                <div class="uk-form-row">
                    {!! Form::label('template-name', 'Upload Name', ['class' => 'uk-form-label']) !!}
                    {!! Form::text('template_name', $upload_name_prefix.' - ', ['class' => 'uk-width-1-1', 'id' => 'template-name']) !!}
                </div>
                <div class="uk-form-row uk-align-right">
                    <a id="toggle-upload" name="upload" class="uk-width-1-1 uk-button uk-button-primary uk-button-large" href="javascript: void(0);">Process</a>
                </div>

            </fieldset>

            {!! Form::hidden('_token', csrf_token(), ['id' => 'csrf_token']) !!}
            {!! Form::hidden('action_by', $action_by, ['id' => 'csrf_token']) !!}
            {!! Form::close() !!}
        </div>
    </div>
</div>
@stop

@section('js')
<script src="{{ asset('/libs/components/jquery/src/upload.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<script src="{{ asset('/libs/classes/uploads.js') }}"></script>
<script src="{{ asset('/libs/classes/documents.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function () {
        Documents.index();
    })
</script>
@stop