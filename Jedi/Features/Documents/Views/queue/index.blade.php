@extends('layouts.default')

@section('title')
    @parent
    {{ $title }}
@stop

@section('css')
    <style>
    /*.uk-modal-dialog{*/
        /*padding:0;*/
        /*width:0;*/
        /*color: #fff;*/
    /*}*/
    </style>
@stop

@section('content')
    @include('layouts.partials.menu', ['page' => 'queue'])

    <div id="main" class="uk-container uk-container-center">
        @include('Documents::partials.filters')

        <table class="uk-table uk-table-hover uk-table-striped">
            <thead>
                <tr>
                    <th>Upload</th>
                    <th>Processor</th>
                    <th>Output</th>
                    <th>Arrived</th>
                    <th>Format</th>
                    <th class="uk-text-center">Files</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="table-body-queue">
                @include('Documents::partials.queue-list', ['batch' => $batch])
            </tbody>
        </table>
        {!! Form::hidden('_token', csrf_token(), ['id' => 'csrf_token']) !!}
    </div>
@stop
@section('js')
    <script src="{{ asset('/libs/classes/documents.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            Documents.initList();
            Documents.Events();
        });
    </script>
@stop