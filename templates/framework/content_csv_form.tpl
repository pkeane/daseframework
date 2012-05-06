<div class="container span4">
	<h1>Upload CSV</h1>
	<p>CSV file should contain rows, the first three column of
	which should represent: title, body, type</p>
	<form action="content/csv/form" method="post" class="form-horizontal" enctype="multipart/form-data">
		<label for="uploaded_file">select a file</label>
		<input type="file" name="uploaded_file"/>
		<input type="submit" value="upload csv file"/>
	</form>
</div>
