<?php
session_start();
ini_set('display_errors', 1);
class Action
{
	private $db;

	public function __construct()
	{
		ob_start();
		include '../config/db_connect.php';

		$this->db = $conn;
	}
	function __destruct()
	{
		$this->db->close();
		ob_end_flush();
	}

	function login()
	{
		extract($_POST);
		$type = array("employee_list", "evaluator_list", "users");
		$qry = $this->db->query("SELECT *,concat(firstname,' ',lastname) as name FROM {$type[$login]} where email = '" . $email . "' and password = '" . md5($password) . "'  ");
		if ($qry->num_rows > 0) {
			foreach ($qry->fetch_array() as $key => $value) {
				if ($key != 'password' && !is_numeric($key))
					$_SESSION['login_' . $key] = $value;
			}
			$_SESSION['login_type'] = $login;
			return 1;
		} else {
			return 2;
		}
	}
	function logout()
	{
		session_destroy();
		foreach ($_SESSION as $key => $value) {
			unset($_SESSION[$key]);
		}
		header("location:../view/login.php");
	}

	function get_emp_tasks2()
	{
		extract($_POST);
		$qry = "select * from task_list where employee_id = $employee_id";
		$res = $this->db->query($qry);
		$tasks = [];
		$massege = [];

		if ($res) {
			while ($row = $res->fetch_assoc()) {
				$tasks[] = $row;
			}

			$massege = [
				"status" => true,
				"data" => $tasks
			];
		} else {
			$massege = [
				"status" => false,
				"data" => $this->db->error
			];
		}

		echo json_encode($massege);

	}
	function login2()
	{
		extract($_POST);
		$qry = $this->db->query("SELECT *,concat(lastname,', ',firstname,' ',middlename) as name FROM students where student_code = '" . $student_code . "' ");
		if ($qry->num_rows > 0) {
			foreach ($qry->fetch_array() as $key => $value) {
				if ($key != 'password' && !is_numeric($key))
					$_SESSION['rs_' . $key] = $value;
			}
			return 1;
		} else {
			return 3;
		}
	}

	function addAward()
	{
		extract($_POST);

		$query = "insert into award(employee_id,task_id,award) values($employee_id,$task_id,'$award')";
		$exec = $this->db->query($query);
		$massege = [];

		if ($exec) {
			$massege = [
				"status" => true,
				"data" => "seccessfuly added"
			];

		} else {
			$massege = [
				"status" => false,
				"data" => $this->db->error
			];
		}

		echo json_encode($massege);
	}

	function updateAward()
	{
		extract($_POST);
		$query = "update award set employee_id=$employee_id,task_id=$task_id,award='$award' where id=$id";
		$exec = $this->db->query($query);

		$massege = [];

		if ($exec) {
			$massege = [
				"status" => true,
				"data" => "seccessfuly updated"
			];

		} else {
			$massege = [
				"status" => false,
				"data" => $this->db->error
			];
		}
		echo json_encode($massege);
	}

	function delete_award()
	{
		extract($_POST);

		$query = "delete from award where id=$id";
		$exec = $this->db->query($query);
		$massege = [];

		if ($exec) {
			$massege = [
				"status" => true,
				"data" => "seccessfully deleted"
			];
		} else {
			$massege = [
				"status" => true,
				"data" => $conn->error
			];
		}
		echo json_encode($massege);
	}
	function save_user()
	{
		extract($_POST);
		$data = "";
		foreach ($_POST as $k => $v) {
			if (!in_array($k, array('id', 'cpass', 'password')) && !is_numeric($k)) {
				if (empty($data)) {
					$data .= " $k='$v' ";
				} else {
					$data .= ", $k='$v' ";
				}
			}
		}
		if (!empty($password)) {
			$data .= ", password=md5('$password') ";

		}
		$check = $this->db->query("SELECT * FROM users where email ='$email' " . (!empty($id) ? " and id != {$id} " : ''))->num_rows;
		if ($check > 0) {
			return 2;
			exit;
		}
		if (isset($_FILES['img']) && $_FILES['img']['tmp_name'] != '') {
			$fname = strtotime(date('y-m-d H:i')) . '_' . $_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'], '../assets/uploads/' . $fname);
			$data .= ", avatar = '$fname' ";

		}
		if (empty($id)) {
			if (!isset($_FILES['img']) || (isset($_FILES['img']) && $_FILES['img']['tmp_name'] == '')) {
				$data .= ", avatar = 'no-image-available.png' ";
			}
			$save = $this->db->query("INSERT INTO users set $data");
		} else {
			$save = $this->db->query("UPDATE users set $data where id = $id");
		}

		if ($save) {
			return 1;
		}
	}
	function signup()
	{
		extract($_POST);
		$data = "";
		foreach ($_POST as $k => $v) {
			if (!in_array($k, array('id', 'cpass')) && !is_numeric($k)) {
				if ($k == 'password') {
					if (empty($v))
						continue;
					$v = md5($v);

				}
				if (empty($data)) {
					$data .= " $k='$v' ";
				} else {
					$data .= ", $k='$v' ";
				}
			}
		}

		$check = $this->db->query("SELECT * FROM users where email ='$email' " . (!empty($id) ? " and id != {$id} " : ''))->num_rows;
		if ($check > 0) {
			return 2;
			exit;
		}
		if (isset($_FILES['img']) && $_FILES['img']['tmp_name'] != '') {
			$fname = strtotime(date('y-m-d H:i')) . '_' . $_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'], '../assets/uploads/' . $fname);
			$data .= ", avatar = '$fname' ";

		}
		if (empty($id)) {
			if (!isset($_FILES['img']) || (isset($_FILES['img']) && $_FILES['img']['tmp_name'] == '')) {
				$data .= ", avatar = 'no-image-available.png' ";
			}
			$save = $this->db->query("INSERT INTO users set $data");

		} else {
			$save = $this->db->query("UPDATE users set $data where id = $id");
		}

		if ($save) {
			if (empty($id))
				$id = $this->db->insert_id;
			foreach ($_POST as $key => $value) {
				if (!in_array($key, array('id', 'cpass', 'password')) && !is_numeric($key))
					$_SESSION['login_' . $key] = $value;
			}
			$_SESSION['login_id'] = $id;
			if (isset($_FILES['img']) && !empty($_FILES['img']['tmp_name']))
				$_SESSION['login_avatar'] = $fname;
			return 1;
		}
	}

	function update_user()
	{
		extract($_POST);
		$data = "";
		foreach ($_POST as $k => $v) {
			if (!in_array($k, array('id', 'cpass', 'table', 'password')) && !is_numeric($k)) {

				if (empty($data)) {
					$data .= " $k='$v' ";
				} else {
					$data .= ", $k='$v' ";
				}
			}
		}
		$type = array("employee_list", "evaluator_list", "users");
		$check = $this->db->query("SELECT * FROM {$type[$_SESSION['login_type']]} where email ='$email' " . (!empty($id) ? " and id != {$id} " : ''))->num_rows;
		if ($check > 0) {
			return 2;
			exit;
		}
		if (isset($_FILES['img']) && $_FILES['img']['tmp_name'] != '') {
			$fname = strtotime(date('y-m-d H:i')) . '_' . $_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'], '../assets/uploads/' . $fname);
			$data .= ", avatar = '$fname' ";

		}
		if (!empty($password))
			$data .= " ,password=md5('$password') ";
		if (empty($id)) {
			if (!isset($_FILES['img']) || (isset($_FILES['img']) && $_FILES['img']['tmp_name'] == '')) {
				$data .= ", avatar = 'no-image-available.png' ";
			}
			$save = $this->db->query("INSERT INTO {$type[$_SESSION['login_type']]} set $data");
		} else {
			$save = $this->db->query("UPDATE {$type[$_SESSION['login_type']]} set $data where id = $id");
		}

		if ($save) {
			foreach ($_POST as $key => $value) {
				if ($key != 'password' && !is_numeric($key))
					$_SESSION['login_' . $key] = $value;
			}
			if (isset($_FILES['img']) && !empty($_FILES['img']['tmp_name']))
				$_SESSION['login_avatar'] = $fname;
			return 1;
		}
	}
	function delete_user()
	{
		extract($_POST);
		$delete = $this->db->query("DELETE FROM users where id = " . $id);
		if ($delete)
			return 1;
	}
	function save_system_settings()
	{
		extract($_POST);
		$data = '';
		foreach ($_POST as $k => $v) {
			if (!is_numeric($k)) {
				if (empty($data)) {
					$data .= " $k='$v' ";
				} else {
					$data .= ", $k='$v' ";
				}
			}
		}
		if ($_FILES['cover']['tmp_name'] != '') {
			$fname = strtotime(date('y-m-d H:i')) . '_' . $_FILES['cover']['name'];
			$move = move_uploaded_file($_FILES['cover']['tmp_name'], '../assets/uploads/' . $fname);
			$data .= ", cover_img = '$fname' ";

		}
		$chk = $this->db->query("SELECT * FROM system_settings");
		if ($chk->num_rows > 0) {
			$save = $this->db->query("UPDATE system_settings set $data where id =" . $chk->fetch_array()['id']);
		} else {
			$save = $this->db->query("INSERT INTO system_settings set $data");
		}
		if ($save) {
			foreach ($_POST as $k => $v) {
				if (!is_numeric($k)) {
					$_SESSION['system'][$k] = $v;
				}
			}
			if ($_FILES['cover']['tmp_name'] != '') {
				$_SESSION['system']['cover_img'] = $fname;
			}
			return 1;
		}
	}
	function save_image()
	{
		extract($_FILES['file']);
		if (!empty($tmp_name)) {
			$fname = strtotime(date("Y-m-d H:i")) . "_" . (str_replace(" ", "-", $name));
			$move = move_uploaded_file($tmp_name, '../assets/uploads/' . $fname);
			$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, 5)) == 'https' ? 'https' : 'http';
			$hostName = $_SERVER['HTTP_HOST'];
			$path = explode('/', $_SERVER['PHP_SELF']);
			$currentPath = '/' . $path[1];
			if ($move) {
				return $protocol . '://' . $hostName . $currentPath . '../assets/uploads/' . $fname;
			}
		}
	}
	function save_department()
	{
		extract($_POST);
		$data = "";
		foreach ($_POST as $k => $v) {
			if (!in_array($k, array('id', 'user_ids')) && !is_numeric($k)) {
				if (empty($data)) {
					$data .= " $k='$v' ";
				} else {
					$data .= ", $k='$v' ";
				}
			}
		}

		$chk = $this->db->query("SELECT * FROM department_list where department = '$department' and id != '{$id}' ")->num_rows;
		if ($chk > 0) {
			return 2;
		}
		if (isset($user_ids)) {
			$data .= ", user_ids='" . implode(',', $user_ids) . "' ";
		}
		if (empty($id)) {
			$save = $this->db->query("INSERT INTO department_list set $data");
		} else {
			$save = $this->db->query("UPDATE department_list set $data where id = $id");
		}
		if ($save) {
			return 1;
		}
	}
	function delete_department()
	{
		extract($_POST);
		$delete = $this->db->query("DELETE FROM department_list where id = $id");
		if ($delete) {
			return 1;
		}
	}
	function save_designation()
	{
		extract($_POST);
		$data = "";
		foreach ($_POST as $k => $v) {
			if (!in_array($k, array('id', 'user_ids')) && !is_numeric($k)) {
				if (empty($data)) {
					$data .= " $k='$v' ";
				} else {
					$data .= ", $k='$v' ";
				}
			}
		}
		$chk = $this->db->query("SELECT * FROM jobs where job = '$designation' and id != '{$id}' ")->num_rows;
		if ($chk > 0) {
			return 2;
		}
		if (isset($user_ids)) {
			$data .= ", user_ids='" . implode(',', $user_ids) . "' ";
		}
		if (empty($id)) {
			$save = $this->db->query("insert into jobs(job,description) values('$designation','$description')");
		} else {
			$save = $this->db->query("update jobs set job='$designation',description='$description' where id=$id");
		}
		if ($save) {
			return 1;
		}
	}
	function addJob()
	{
		extract($_POST);

		$query = "insert into jobs(job,description) values('$job','$description')";
		$exec = $this->db->query($query);
		$massege = [];

		if ($exec) {
			$massege = [
				"status" => true,
				"data" => "seccessfuly added"
			];

		} else {
			$massege = [
				"status" => false,
				"data" => $this->db->error
			];
		}

		echo json_encode($massege);
	}

	function updateJob()
	{
		extract($_POST);
		$query = "update jobs set job='$job',description='$description' where id=$id";
		$exec = $this->db->query($query);

		$massege = [];

		if ($exec) {
			$massege = [
				"status" => true,
				"data" => "seccessfuly updated"
			];

		} else {
			$massege = [
				"status" => false,
				"data" => $this->db->error
			];
		}
		echo json_encode($massege);
	}
	function delete_designation()
	{
		extract($_POST);
		$delete = $this->db->query("DELETE FROM jobs where id = $id");
		if ($delete) {
			return 1;
		}
	}
	function save_employee()
	{
		extract($_POST);
		$data = "";
		foreach ($_POST as $k => $v) {
			if (!in_array($k, array('id', 'cpass', 'password')) && !is_numeric($k)) {
				if (empty($data)) {
					$data .= " $k='$v' ";
				} else {
					$data .= ", $k='$v' ";
				}
			}
		}
		if (!empty($password)) {
			$data .= ", password=md5('$password') ";

		}
		$check = $this->db->query("SELECT * FROM employee_list where email ='$email' " . (!empty($id) ? " and id != {$id} " : ''))->num_rows;
		if ($check > 0) {
			return 2;
			exit;
		}
		if (isset($_FILES['img']) && $_FILES['img']['tmp_name'] != '') {
			$fname = strtotime(date('y-m-d H:i')) . '_' . $_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'], '../assets/uploads/' . $fname);
			$data .= ", avatar = '$fname' ";

		}
		if (empty($id)) {
			if (!isset($_FILES['img']) || (isset($_FILES['img']) && $_FILES['img']['tmp_name'] == '')) {
				$data .= ", avatar = 'no-image-available.png' ";
			}
			$save = $this->db->query("INSERT INTO employee_list set $data");
		} else {
			$save = $this->db->query("UPDATE employee_list set $data where id = $id");
		}

		if ($save) {
			return 1;
		}
	}
	function delete_employee()
	{
		extract($_POST);
		$delete = $this->db->query("DELETE FROM employee_list where id = " . $id);
		if ($delete)
			return 1;
	}
	function save_evaluator()
	{
		extract($_POST);
		$data = "";
		foreach ($_POST as $k => $v) {
			if (!in_array($k, array('id', 'cpass', 'password')) && !is_numeric($k)) {
				if (empty($data)) {
					$data .= " $k='$v' ";
				} else {
					$data .= ", $k='$v' ";
				}
			}
		}
		if (!empty($password)) {
			$data .= ", password=md5('$password') ";

		}
		$check = $this->db->query("SELECT * FROM evaluator_list where email ='$email' " . (!empty($id) ? " and id != {$id} " : ''))->num_rows;
		if ($check > 0) {
			return 2;
			exit;
		}
		if (isset($_FILES['img']) && $_FILES['img']['tmp_name'] != '') {
			$fname = strtotime(date('y-m-d H:i')) . '_' . $_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'], '../assets/uploads/' . $fname);
			$data .= ", avatar = '$fname' ";

		}
		if (empty($id)) {
			if (!isset($_FILES['img']) || (isset($_FILES['img']) && $_FILES['img']['tmp_name'] == '')) {
				$data .= ", avatar = 'no-image-available.png' ";
			}
			$save = $this->db->query("INSERT INTO evaluator_list set $data");
		} else {
			$save = $this->db->query("UPDATE evaluator_list set $data where id = $id");
		}

		if ($save) {
			return 1;
		}
	}
	function delete_evaluator()
	{
		extract($_POST);
		$delete = $this->db->query("DELETE FROM evaluator_list where id = " . $id);
		if ($delete)
			return 1;
	}
	function save_task()
	{
		extract($_POST);
		$data = "";
		foreach ($_POST as $k => $v) {
			if (!in_array($k, array('id')) && !is_numeric($k)) {
				if ($k == 'description')
					$v = htmlentities(str_replace("'", "&#x2019;", $v));
				if (empty($data)) {
					$data .= " $k='$v' ";
				} else {
					$data .= ", $k='$v' ";
				}
			}
		}
		if (empty($id)) {
			$save = $this->db->query("INSERT INTO task_list set $data");
		} else {
			$save = $this->db->query("UPDATE task_list set $data where id = $id");
		}
		if ($save) {
			return 1;
		}
	}
	function delete_task()
	{
		extract($_POST);
		$delete = $this->db->query("DELETE FROM task_list where id = $id");
		if ($delete) {
			return 1;
		}
	}
	function save_progress()
	{
		extract($_POST);
		$data = "";
		foreach ($_POST as $k => $v) {
			if (!in_array($k, array('id')) && !is_numeric($k)) {
				if ($k == 'progress')
					$v = htmlentities(str_replace("'", "&#x2019;", $v));
				if (empty($data)) {
					$data .= " $k='$v' ";
				} else {
					$data .= ", $k='$v' ";
				}
			}
		}
		if (!isset($is_complete))
			$data .= ", is_complete=0 ";
		if (empty($id)) {
			$save = $this->db->query("INSERT INTO task_progress set $data");
		} else {
			$save = $this->db->query("UPDATE task_progress set $data where id = $id");
		}
		if ($save) {
			if (!isset($is_complete))
				$this->db->query("UPDATE task_list set status = 1 where id = $task_id ");
			else
				$this->db->query("UPDATE task_list set status = 2 where id = $task_id ");
			return 1;
		}
	}
	function delete_progress()
	{
		extract($_POST);
		$delete = $this->db->query("DELETE FROM task_progress where id = $id");
		if ($delete) {
			return 1;
		}
	}
	function save_evaluation()
	{
		extract($_POST);
		$data = "";
		foreach ($_POST as $k => $v) {
			if (!in_array($k, array('id')) && !is_numeric($k)) {
				if (empty($data)) {
					$data .= " $k='$v' ";
				} else {
					$data .= ", $k='$v' ";
				}
			}
		}
		$data .= ", evaluator_id = {$_SESSION['login_id']} ";
		if (empty($id)) {
			$save = $this->db->query("INSERT INTO ratings set $data");
		} else {
			$save = $this->db->query("UPDATE ratings set $data where id = $id");
		}
		if ($save) {
			if (!isset($is_complete))
				return 1;
		}
	}
	function delete_evaluation()
	{
		extract($_POST);
		$delete = $this->db->query("DELETE FROM ratings where id = $id");
		if ($delete) {
			return 1;
		}
	}
	function get_emp_tasks()
	{
		extract($_POST);

		if (!isset($task_id))
			$get = $this->db->query("SELECT * FROM task_list where employee_id = $employee_id and status = 2 and id not in (SELECT task_id FROM ratings) ");
		else
			$get = $this->db->query("SELECT * FROM task_list where employee_id = $employee_id and status = 2 and id not in (SELECT task_id FROM ratings where task_id !='$task_id') ");
		$data = [];
		$message = [];

		while ($row = $get->fetch_assoc()) {
			$data []= $row;
		}

		$message = [
			'status' => true,
            'data' => $data
		];

		echo json_encode($message);
	}
	function get_progress()
	{
		extract($_POST);
		$get = $this->db->query("SELECT p.*,concat(u.firstname,' ',u.middlename) as uname,u.avatar FROM task_progress p inner join task_list t on t.id = p.task_id inner join employee_list u on u.id = t.employee_id where p.task_id = $task_id order by unix_timestamp(p.date_created) desc ");
		$data = array();
		while ($row = $get->fetch_assoc()) {
			$row['uname'] = ucwords($row['uname']);
			$row['progress'] = html_entity_decode($row['progress']);
			$row['date_created'] = date("M d, Y", strtotime($row['date_created']));
			$data[] = $row;
		}
		return json_encode($data);
	}
	function get_report()
	{
		extract($_POST);
		$data = array();
		$get = $this->db->query("SELECT t.*,p.name as ticket_for FROM ticket_list t inner join pricing p on p.id = t.pricing_id where date(t.date_created) between '$date_from' and '$date_to' order by unix_timestamp(t.date_created) desc ");
		while ($row = $get->fetch_assoc()) {
			$row['date_created'] = date("M d, Y", strtotime($row['date_created']));
			$row['name'] = ucwords($row['name']);
			$row['adult_price'] = number_format($row['adult_price'], 2);
			$row['child_price'] = number_format($row['child_price'], 2);
			$row['amount'] = number_format($row['amount'], 2);
			$data[] = $row;
		}
		return json_encode($data);

	}
}