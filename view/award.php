<?php include '../config/db_connect.php' ?>
<div class="col-lg-12">
    <div class="card card-outline card-primary">
        <div class="card-header">
        <?php if ($_SESSION['login_type'] == 2): ?>
            <div class="card-tools">
                <a class="btn btn-block btn-sm btn-default btn-flat border-primary new_award"
                    href="javascript:void(0)"><i class="fa fa-plus"></i> Add New</a>
            </div>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <table class="table tabe-hover table-bordered" id="list">
                <colgroup>
                    <col width="5%">
                    <col width="25%">
                    <col width="25%">
                    <col width="10%">
                    <col width="25%">
                    <col width="10%">
                </colgroup>
                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th>Task</th>
                        <th>Employee_name</th>
                        <th>Perfomance_avrage</th>
                        <th>Award</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    $where = "";
					if($_SESSION['login_type'] == 0)
						$where = " where a.employee_id = {$_SESSION['login_id']} ";

                    $qry =  $conn->query("SELECT a.*,concat(e.firstname,', ',e.middlename,' ',e.lastname) as name,t.task,((((r.efficiency + r.timeliness + r.quality + r.accuracy)/4)/5) * 100) as pa FROM award a inner join employee_list e on e.id = a.employee_id inner join task_list t on t.id = a.task_id inner join ratings r on r.task_id = a.task_id $where order by concat(e.firstname,', ',e.middlename,' ',e.lastname) asc");
                    while ($row = $qry->fetch_assoc()):
                        ?>
                        <tr>
                            <th class="text-center">
                                <?php echo $i++ ?>
                            </th>
                            <td><b><?php echo ($row['task']) ?></b></td>
                            <td><b><?php echo ucwords($row['name']) ?></b></td>
                            <td><b><?php echo number_format($row['pa'],2)."%" ?></b></td>
                            <td><b><?php echo ($row['award']) ?></b></td>

                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="javascript:void(0)" data-id='<?php echo $row['id'] ?>'
                                        class="btn btn-primary btn-flat manage_department">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger btn-flat delete_department"
                                        data-id="<?php echo $row['id'] ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('#list').dataTable()
        $('.new_award').click(function () {
            uni_modal("New Award", "manage_award.php")
        })
        $('.manage_department').click(function () {
            uni_modal("Manage Department", "manage_department.php?id=" + $(this).attr('data-id'))
        })
        $('.delete_department').click(function () {
            _conf("Are you sure to delete this award?", "delete_award", [$(this).attr('data-id')])
        })
    })

    function delete_award($id) {
        start_load()
        $.ajax({
            url: '../api/ajax.php?action=delete_award',
            method: 'POST',
            dataType: 'json',
            data: { id: $id },
            success: function (resp) {
                console.log(resp);
                if (resp.status) {
                    alert_toast("Data successfully deleted", 'success')
                    setTimeout(function () {
                        location.reload()
                    }, 1500)

                }
                if (!resp.status) {
                    alert_toast(resp.data, 'error')
                    end_load()
                }
            }
        })
    }
</script>