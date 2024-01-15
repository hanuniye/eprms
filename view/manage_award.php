<?php
include '../config/db_connect.php';
if (isset($_GET['id'])) {
    $qry = $conn->query("SELECT * FROM award where id={$_GET['id']}")->fetch_array();
    foreach ($qry as $k => $v) {
        $$k = $v;
    }
}
?>
<div class="container-fluid">
    <form action="" id="manage-award">
        <input type="hidden" name="id" id="id" value="<?php echo isset($id) ? $id : '' ?>">
        <div class="form-group">
            <label for="" class="control-label">Employee</label>
            <select name="employee_id" id="employee_id" class="form-control form-control-sm select2" required
                value="<?php echo isset($employee_id) ? $employee_id : '' ?>">

                <option value="">select employee</option>
                <?php
                $employees = $conn->query("SELECT *,concat(firstname,' ',middlename,' ',lastname) as name FROM employee_list");
                while ($row = $employees->fetch_assoc()):
                    ?>
                    <option value="<?php echo $row['id'] ?>" <?php
                       echo isset($employee_id) && $employee_id == $row['id'] ? 'selected' : '' ?>>
                        <?php echo $row['name'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="" class="control-label">Task</label>
            <select name="task_id" id="task_id" class="form-control form-control-sm">
            </select>
        </div>

        <div class="form-group">
            <label for="" class="control-label">Award</label>
            <input name="award" id="award" class="form-control form-control-sm" placeholder="Award" required
                value="<?php echo isset($award) ? $award : '' ?>" />
        </div>
    </form>
</div>

<script>

    if ("<?php echo isset($employee_id) ?>") {
        update_emp(false, true);
    }


    $('#manage-award').submit(function (e) {
        e.preventDefault();
        if ($("#employee_id").val() == "" || $("#task_id").val() == "" || $("#task_id").val() == null || $("#award").val() == "") {
            alert_toast("Please fill all fields.", "warning");
            return false;
        }

        let data = {};
        let url = '';

        if($("#id").val() == ""){
            url = '../api/ajax.php?action=addAward';
            data = $(this).serialize();
        }else{
            url = '../api/ajax.php?action=updateAward';
            data = {
                id: $("#id").val(),
                employee_id: $("#employee_id").val(),
                task_id: $("#task_id").val(),
                award: $("#award").val()
            };
        }

        start_load()
        $('#msg').html('')
        $.ajax({
            url: url,
            method: 'POST',
            dataType: 'json',
            data: data,
            success: function (resp) {
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


    $('#employee_id').change(function () {
        update_emp(true)
    })

    function update_emp($isLoading = false, $isUpdate = false) {
        if ($isLoading) {
            start_load();
        }

        if($('#employee_id').val() == ''){
            end_load();
            $("#task_id").html("`<option value=''>select task</option>")
            return false;
        }

        $.ajax({
            url: '../api/ajax.php?action=get_emp_tasks2',
            method: 'POST',
            dataType: "JSON",
            data: { employee_id: $('#employee_id').val() },
            error: (err) => {
                alert_toast("An error occured", 'error')
                console.log(err)
                end_load()
            },
            success: function (resp) {
                console.log(resp.data)
                let task_id = $('#task_id');
                let option = $isUpdate ? "" : "`<option value=''>select task</option>";

                resp.data.forEach(data => {
                    option += `<option value='${data.id}'>${data.task}</option>`;
                })
                task_id.html(option);
                end_load();
                $("#task_id").val("<?php echo isset($task_id) ? $task_id : '' ?>");
            },

        })
    }



</script>