@extends('layouts.default')

@section('title')
    @parent
    {{ $title }}
@stop

@section('css')
@stop
@section('content')
    @include('layouts.partials.menu', ['page' => 'queue'])

    @if($document['status'])
        <div id="main" class="uk-container uk-container-center">
            <div class="uk-grid">
                <div class="uk-width-6-10">
                    <div class="uk-panel uk-panel-box jedi-doc-preview" data-uk-sticky>
                        @if($document['data']['file_type'] == "PDF")
                            <div class="uk-panel-badge uk-badge"><i class="uk-icon-file-pdf-o"> PDF</i></div>
                        @else
                            <div class="uk-panel-badge uk-badge"><i class="uk-icon-file-excel-o"> XLS</i></div>
                        @endif
                        <div class="uk-panel-title uk-text-small uk-float-left">{{ $document['data']['po'] }} <br> {{ $document['data']['created_at'] }}</div>
                        {{--<div class="uk-float-left uk-margin-large-left uk-margin-large-right"><button class="uk-button uk-button-small uk-disabled"><i class="uk-icon-arrow-left"> Prev </i></button> <button class="uk-button uk-button-small"><i class="uk-icon-arrow-right"> Next </i></button>   Doc 1/4 <button class="uk-button uk-button-small uk-disabled"><i class="uk-icon-arrow-left"> Prev </i></button> <button class="uk-button uk-button-small"><i class="uk-icon-arrow-right"> Next </i></button></div>--}}
                        <div>
                            <!-- this is where the zoom action happens -->
                            @if($document['data']['file_type'] == "PDF")
                                {{--<?php $newFileName = str_replace('.pdf','',$document['data']['po']); ?>--}}
                                {{--@for($x = 0; $x < $document['data']->image_count; $x++)--}}
                                    {{--<img class="zoom_01" src="{{ asset('/tmp/pos/'.$newFileName.'-'.$x.'.jpg') }}" data-zoom-image="{{ asset('/tmp/pos/J&J scan Project-0.jpg') }}" alt="Document" />--}}
                                {{--@endfor--}}
                            @else
                                {{--<iframe class="zoho-iframe-viewer" style="height: 555px; width: 100%;" src="https://sheet.zoho.com/sheet/view.do?url={{$xls_file}}&name={{$document['data']['po']}}" frameborder="0"></iframe>--}}
                                <iframe src="http://docs.google.com/gview?url={{ $xls_file }}&embedded=true" style="width:600px; height:600px;" frameborder="0"></iframe>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="uk-width-4-10" style="height: 650px; overflow-y: scroll">
                    <div class="uk-panel uk-panel-box jedi-review-panel">
                        {!! Form::open(["id" => "upload-form", "class" => "uk-panel uk-panel-box uk-form uk-form-stacked" ]) !!}
                        Current Status:

                        @if($document['data']['status'] == 'Pending')
                            <i class="uk-icon-dot-circle-o uk-text-primary"> {{ $document['data']['status'] }}</i>
                        @elseif($document['data']['status'] == 'Reject')
                            <i class="uk-icon-times-circle uk-text-danger"> {{ $document['data']['status'] }}</i>
                        @elseif($document['data']['status'] == 'Accept' or $document['data']['status'] == 'Send')
                            <i class="uk-icon-check-circle uk-text-success"> {{ $document['data']['status'] }}</i>
                        @endif


                        <div class="jedi-review-actions">
                            <a href="javascript:void(0)" data-id="{{ $document['data']['document_details_id'] }}" data-batch="{{ $document['data']['batch_id'] }}" data-status="reject" class="uk-button uk-button-danger toggle-document-status"><i class="uk-icon-times-circle-o"> REJECT</i></a>
                            <a href="javascript:void(0)" data-id="{{ $document['data']['document_details_id'] }}" data-batch="{{ $document['data']['batch_id'] }}" data-status="accept" class="uk-button uk-button-primary toggle-document-status"><i class="uk-icon-check-circle"> ACCEPT</i></a>
                            {{--<a href="javascript:void(0)" data-id="{{ $document['data']['document_details_id'] }}" data-batch="{{ $document['data']['batch_id'] }}" data-status="send" class="uk-button uk-button-success toggle-document-status"><i class="uk-icon-sign-in"> SEND</i></a>--}}
                        </div>

                        {!! Form::hidden('_token', csrf_token(), ['id' => 'csrf_token']) !!}
                        {!! Form::hidden('action_by', $action_by, ['id' => 'csrf_token']) !!}
                        {!! Form::close() !!}
                    </div>
                    <?php
                    $extras = json_decode($document['data']['extras'],true);
                    ?>
                    <div class="uk-panel uk-panel-box jedi-review-panel">
                        <div class="uk-panel-title uk-text-small jedi-review-header">HEADER</div>
                        <form class="uk-form uk-form-horizontal">
                            @if($extras['companyname'] == "Robinsons Incorporated")
                                <fieldset data-uk-margin>
                                    <div class="uk-form-row">
                                        <label for="head_vendor" class="uk-form-label">Vendor</label>
                                        <input type="text" name="head_vendor" id="head_vendor" value="{{$extras['vendor']}}" class="uk-form-small uk-form-blank">
                                    </div>
                                    <div class="uk-form-row">
                                        <label for="head_po_number" class="uk-form-label">PO Number</label>
                                        <input type="text" name="head_po_number" id="head_po_number" value="{{ $extras['ponum'] }}" class="uk-form-small uk-form-blank">
                                    </div>
                                    <div class="uk-form-row">
                                        <label for="head_approved_date" class="uk-form-label">Approved Date</label>
                                        <input type="text" name="head_approved_date" id="head_approved_date" value="{{$extras['approveddate']}}" class="uk-form-small uk-form-blank">
                                    </div>
                                    <div class="uk-form-row">
                                        <label for="head_delivery_date" class="uk-form-label">Delivery Date</label>
                                        <input type="text" name="head_delivery_date" id="head_delivery_date" value="{{$extras['deliverydate']}}" class="uk-form-small uk-form-blank">
                                    </div>
                                </fieldset>
                            @else
                                <?php
                                $today     = new \DateTime();
                                $friday = $today->modify('friday this week');
                                $delivery_date = $friday->format('m/d/Y');
                                ?>
                                <fieldset data-uk-margin>
                                    <div class="uk-form-row">
                                        <label for="head_po_number" class="uk-form-label">PO Number</label>
                                        <input type="text" name="head_po_number" id="head_po_number" value="{{ $document['data']['po'] }}" class="uk-form-small uk-form-blank">
                                    </div>
                                    <div class="uk-form-row">
                                        <label for="head_approved_date" class="uk-form-label">Approved Date</label>
                                        <input type="text" name="head_approved_date" id="head_approved_date" value="{{date('m/d/Y')}}" class="uk-form-small uk-form-blank">
                                    </div>
                                    <div class="uk-form-row">
                                        <label for="head_delivery_date" class="uk-form-label">Delivery Date</label>
                                        <input type="text" name="head_delivery_date" id="head_delivery_date" value="{{$delivery_date}}" class="uk-form-small uk-form-blank">
                                    </div>
                                </fieldset>
                            @endif
                        </form>
                    </div>
                    {{--<div class="uk-panel uk-panel-box jedi-review-panel">--}}
                        {{--<div class="uk-panel-title uk-text-small jedi-review-header">HEADER</div>--}}
                        {{--<form class="uk-form uk-form-horizontal">--}}
                            {{--<fieldset data-uk-margin>--}}
                                {{--<div class="uk-form-row">--}}
                                    {{--<label for="head_vendor" class="uk-form-label">Vendor</label>--}}
                                    {{--<input type="text" name="head_vendor" id="head_vendor" value="100006" class="uk-form-small uk-form-blank">--}}
                                {{--</div>--}}
                                {{--<div class="uk-form-row">--}}
                                    {{--<label for="head_po_number" class="uk-form-label">PO Number</label>--}}
                                    {{--<input type="text" name="head_po_number" id="head_po_number" value="{{ $document['data']['po'] }}" class="uk-form-small uk-form-blank">--}}
                                {{--</div>--}}
                                {{--<div class="uk-form-row">--}}
                                    {{--<label for="head_approved_date" class="uk-form-label">Approved Date</label>--}}
                                    {{--<input type="text" name="head_approved_date" id="head_approved_date" value="6 Feb 2015" class="uk-form-small uk-form-blank">--}}
                                {{--</div>--}}
                                {{--<div class="uk-form-row">--}}
                                    {{--<label for="head_delivery_date" class="uk-form-label">Delivery Date</label>--}}
                                    {{--<input type="text" name="head_delivery_date" id="head_delivery_date" value="11 Feb 2015" class="uk-form-small uk-form-blank">--}}
                                {{--</div>--}}
                            {{--</fieldset>--}}

                        {{--</form>--}}
                    {{--</div>--}}

                    @include('Documents::partials.meta-details', ['meta' => $meta, 'po' => $document['data']['po']])
                </div>
            </div>
        </div>
    @endif

@stop
@section('js')
    <script src="{{ asset('/libs/components/jquery/src/jquery.elevatezoom.js') }}"></script>
    <script src="{{ asset('/libs/components/jquery/src/sticky.js') }}"></script>
    <script src="{{ asset('/libs/classes/documents.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            Documents.Process();
        });
    </script>
@stop