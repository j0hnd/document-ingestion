<div id='div' style="height: 200px; overflow:auto">
	<table border=1 cellpadding="0" cellspacing="0" height="200px">
		
		@if($results == null)
			<tr>
				<td width='1000' style=" font-size: 18px"><center> No Fieldname Available </center></td>
			</tr>
			
		@else
			@foreach($results as $row)
			<tr>
				<td width='500' style="padding-left: 15px; font-size: 18px"> {{ ucfirst($row->fieldname) }} </td>
				<td width='30%'>
					<a id='add-fieldname' data-name="{{$row->fieldname}}" style="font-size: 18px;padding-left: 20px;" title=" {{ $row->fieldname }} ">Add </a>
					<label style="font-size: 18px"> | </label>
					<a id='edit-fieldname' data-name="{{$row->fieldname}}" style="font-size: 18px" title=" {{ $row->fieldname }} "> Edit </a>
					<label style="font-size: 18px"> | </label>
					<a id='delete-fieldname' data-name="{{$row->fieldname}}" style="font-size: 18px" title=" {{ $row->fieldname }} "> Delete</a>
				</td>
			</tr>
			@endforeach
			
		@endif
	</table>
</div>	
<br>

<label>Add Field: </label>
<input type="text" id="txt_fieldname"/>
<input type="hidden" id="token" name="_token" value="{{ csrf_token() }}" />

<a id="create-fieldname" style="font-size: 17px">Go</a>
