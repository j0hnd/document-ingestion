<form method='post' enctype="multipart/form-data">
	<input type="file" name='test_file' id='test_file'>
	<button id="test_upload">Submit</button>
	<input type='hidden' name='_token' value='{{csrf_token()}}' >
</form>