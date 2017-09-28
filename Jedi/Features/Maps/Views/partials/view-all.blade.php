<center>
	<div id="view-div">
<form id='frm-view-all'></form>
<table border="1" cellpadding="0" cellspacing="0" width="95%" style="border-collapse:collapse;">
	<tr bgcolor="#FCAEAE">
		<td width='20px'><input type='checkbox' id='check_all'></td>
		<td><b><center>File Name</td>
		<td width='150px'><b><center>File Size</td>
		<td ><b><center>Company</td>
		<td width='145px'><b><center>Upload Date</td>
		<td width='350px'><b><center>Actions</td>
	</tr>
@if($lists == null)
	<tr>
		<td colspan=5>No Documents Uploaded</td>
	</tr>
@else

	@foreach($lists as $row)
		@if ($row->status == 'mapped')
			<tr bgcolor="">
				<td><input type='checkbox' id='check' value='{{ $row->company }}'></td>
				<td style='padding-left: 5px'>{{ $row->file_name }}</td>
				<td><center>{{ $row->file_size }} KB </td>
				<td style='padding-left: 5px'> {{ $row->company }} </td>
				<td><center>{{ $row->created_at }} </td>
				<td>
					<table>
						<tr>
						    <td width="30px"><button id='view-xml' data-comp="{{$row->company}}" data-name="{{$row->file_name}}" data-mime="{{$row->mime_type}}">View</button></td>
						    <td width="175px"><button id='edit' data-comp="{{$row->company}}" data-name="{{$row->file_name}}" data-mime="{{$row->mime_type}}">Edit</button></td>
						    <td ><button id='delete' data-comp="{{$row->company}}" data-name="{{$row->file_name}}" data-mime="{{$row->mime_type}}">Delete</button></td>
						</tr>
					</table>
				</td>
			</tr>
		@else 
			<tr bgcolor="#F5F7A9">
				<td><input type='checkbox' disabled></td>
				<td id='filename' style='padding-left: 5px'>{{ $row->file_name }}</td>
				<td><center>{{ $row->file_size }} KB </td>
				<td id='company' style='padding-left: 5px'> {{ $row->company }} </td>
				<td><center>{{ $row->created_at }} </td>
				<td>
					<table>
						<tr>
						    <td width="175px"><button id='edit' data-comp="{{$row->company}}" data-name="{{$row->file_name}}" data-mime="{{$row->mime_type}}">Edit</button></td>
						    <td><button id='delete' data-comp="{{$row->company}}" data-name="{{$row->file_name}}" data-mime="{{$row->mime_type}}">Delete</button></td>
						</tr>
					</table>
				</td>
			</tr>
	    @endif
			
	@endforeach

@endif

</table>
</center>
</div>