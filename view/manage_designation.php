<?php
include '../config/db_connect.php';
if(isset($_GET['id'])){
	$qry = $conn->query("SELECT * FROM jobs where id={$_GET['id']}")->fetch_array();
	foreach($qry as $k => $v){
		$$k = $v;
	}
}
?>
<div class="container-fluid">
	<form action="" id="manage-designation">
		<input type="hidden" name="id" id="id" value="<?php echo isset($id) ? $id : '' ?>">
		<div id="msg" class="form-group"></div>
		<div class="form-group">
			<label for="designation" class="control-label">Job</label>
			<input type="text" class="form-control form-control-sm" name="job" id="job" value="<?php echo isset($job) ? $job : '' ?>">
		</div>
		<div class="form-group">
			<label for="description" class="control-label">Description</label>
			<textarea name="description" id="description" cols="30" rows="4" class="form-control"><?php echo isset($description) ? $description : '' ?></textarea>
		</div>
	</form>
</div>
<script>
	// $(document).ready(function(){
	// 	$('#manage-designation').submit(function(e){
	// 		e.preventDefault();
	// 		start_load()
	// 		$('#msg').html('')
	// 		$.ajax({
	// 			url:'../api/ajax.php?action=save_designation',
	// 			method:'POST',
	// 			data:$(this).serialize(),
	// 			success:function(resp){
	// 				console.log(resp)
	// 				if(resp == 1){
	// 					alert_toast("Data successfully saved.","success");
	// 					setTimeout(function(){
	// 						location.reload()	
	// 					},1750)
	// 				}else if(resp == 2){
	// 					$('#msg').html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> designation already exist.</div>')
	// 					end_load()
	// 				}
	// 			}
	// 		})
	// 	})
	// })

	
    $('#manage-designation').submit(function (e) {
        e.preventDefault();
        if ($("#description").val() == "" || $("#job").val() == "") {
            alert_toast("Please fill all fields.", "warning");
            return false;
        }

        let data = {};
        let url = '';

        if($("#id").val() == ""){
            url = '../api/ajax.php?action=addJob';
            data = $(this).serialize();
			console.log("add url: " + url, "data: " + data);
        }else{
            url = '../api/ajax.php?action=updateJob';
            data = {
                id: $("#id").val(),
                description: $("#description").val(),
                job: $("#job").val(),
            };


			console.log("update url: " + url, "data: " + data);
        }

        start_load()
        $('#msg').html('')
        $.ajax({
            url: url,
            method: 'POST',
            dataType: 'json',
            data: data,
            success: function (resp) {
				console.log(resp)
                if (resp.status) {
                    alert_toast(resp.data, "success");
                    setTimeout(function () {
                        location.reload()
                    }, 1750)
                } else if (!resp.status) {
                    $('#msg').html(`<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i>${resp.data}.</div>`)
                    end_load()
                }
            }
        })
    })

</script>