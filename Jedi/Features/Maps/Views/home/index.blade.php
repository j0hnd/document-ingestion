@extends('layouts.default')

@section('title')
    @parent
@stop

@section('css')
<style>
#edit_modal
{
    width:500px;
    height:200px;
    margin-top:100px;
    background-color:#8DCACC;
    border-radius:3px;
    box-shadow:0px 0px 10px 0px #424040;
    padding:10px;
    box-sizing:border-box;
    font-family:helvetica;
    visibility:hidden;
    display:none;
    position:absolute;
    top:20px;
    left:500px;
   
}
</style>
@stop

@section('content')

<center>

<br>

    <form enctype="multipart/form-data" id="upload_form" role="form" >
    <br>
        <div class="form-group">
            <input type="file" id="file" name="file" class="form-control" accept="application/pdf,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" required='required' /> 
            <!-- client side validation pdf, xls, xlsx files only -->
            <input type="text" name="company" class="form-control" id="company" placeholder="enter company name" required='required' />
            <button type="button" id='submit'>Upload</button>
            
            <input type="hidden" id="token" name="_token" value="{{ csrf_token() }}" />
            <br>
        </div>
  </form>
 
<form id="header" style="display:none;">
    <h2>Data Mappings</h2>
</form>
<hr>
</center>

<div id='results'>
    @include('Maps::partials.view-all')
</div>

<a id="view-all-data" style="display:none;font-size: 17px">&rarr;View All Data </a>
<a id="back2" style="display:none;font-size: 17px">&rarr;Back</a>
<label style="font-size: 20px;display:none" id="bar" > | </label>
<a id='fieldname' style="display:none; font-size: 17px">Set Up Field</a>

<div id='slider'>
  @include('Maps::partials.setup-field')
</div>

<table id='form' style="display:none;">
    <tr>
        <td>
            <textarea id='textarea' class="lined"  style="display:none;resize:none;width: 760px; height:630px; border:0"  readonly></textarea>
        </td>
        <td id='td_iframe' style="display:none">
            <iframe id='iframe' src='' width='760px' height='630px' frameborder='0'></iframe>

        </td>
        <td width="50%">
            <center>
             @include('Maps::partials.map-details')
            </center>
            <a style="padding-left: 100px; font-size: 17px" id='view-xml-window'>&rarr;view xml in another window</a>
            <a style="padding-left: 80px; font-size:17px;" id='download-xml'>&rarr;download xml</a>
        </td>
    </tr>
</table>
<a style='padding-left:40px;' id='zip'> &rarr; download and zip</a>

@stop

@section('js')
<script src="{{ asset('/libs/classes/maps.js') }}"></script>
<script src="{{ asset('/libs/classes/jquery-linedtextarea.js') }}"></script>

<link href="{{ asset('/css/jquery-linedtextarea.css') }}" rel="stylesheet" type="text/css">

<script type="text/javascript">
    $(document).ready(function () {
        Maps.form();
    });
</script>

@stop


