<?php

require 'Mysql.php';

class View {

	function create_view($username) {
		$mysql = New Mysql();
		$mysql->fetch_times($username);

	}
}

?>