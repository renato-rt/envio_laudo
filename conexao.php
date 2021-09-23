<?php
	error_reporting(0);
	session_start();
	
	function conn(){
		$host   = "localhost"; 
		$dbname = "clinux_crd";
		$user   = "dicomvix";
		$pass   = "system98";
		$port   = "";
		

		return pg_connect("host={$host} dbname={$dbname} user={$user} password={$pass}");
	}
	function criaQueryResult($sql){
		$db = conn();
		$resposta = pg_query($db, $sql);
		return $resposta;
	}
	function resultadoSQLAll($sql){
		$result = criaQueryResult($sql);
		return pg_fetch_all($result);
	}
	function resultadoSQLAssoc($sql){
		$result = criaQueryResult($sql);
		return pg_fetch_assoc($result);
	}
	function resultadoSQLExec($sql){
		$result = criaQueryResult($sql);
		return pg_execute($result);
	}
	function resultadoExec($sql){
		$db = conn();
		$result = pg_prepare($db, "meu_sql", $sql);
		$result = pg_execute($db, "meu_sql", array());
		return $result;
	}
?>