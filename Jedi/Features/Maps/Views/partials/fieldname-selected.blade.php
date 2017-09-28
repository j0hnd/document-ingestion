<div id='div_selected' style="height: 200px; overflow:auto">
	<table border=1 cellpadding="0" cellspacing="0" height="200px">
		@if($selected == null)
			<tr>
				<td width='1000' style=" font-size: 18px"><center> No Fieldname Added </center></td>
			</tr>
			
		@else
			@foreach($selected as $row)
				
					<tr>
						<td><input type='checkbox' id='header' value='{{ $row->fieldname }}'></td>
						<td width='500' style="padding-left: 15px; font-size: 18px" id="selected_fields"> {{ ucfirst($row->fieldname) }} </td>
						<td width='30%'>
							<center>
							<a id='remove-fieldname' data-name="{{$row->fieldname}}" style="font-size: 18px;" title=" {{ $row->fieldname }} "> Remove </a>
							</center>
						</td>
					</tr>

			@endforeach
		@endif
	</table>
</div>	
<br>
<input type="text" style="display:none" id="selected_list"/>
<label><b>* </b><i>Check the Field names that you wants to include in the header.</i></label>
<a id="done-slider" style="float: right; font-size: 17px">Done</a>
