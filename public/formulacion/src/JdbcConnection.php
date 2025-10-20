<?php namespace Reingsys;
require_once JAVA_BRIDGE . '/java/Java.inc';
/*
   JasperReport Class, a util class to create JdbcConnection from PHP
   Created on Oct 19, 2007
   Copyright (C) 2007 bramxy at gmail dot com 

   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 3 of the License, or
   (at your option) any later version.

   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software Foundation,
   Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301  USA

 */

class JdbcConnection {
	private $connectionString;
	private $user;
	private $password;
	private $connection;
	private $driverManager;

	/** 
	 * @desc contructor de la clase inicia los valores
	 * @param string nombre del componente que provee el enlace a la base de datos
	 * @param string cadena de enlace
	 * @param string nombre de usuario
	 * @param string password
	 * @example $connection = new JdbcConnection("org.postgresql.Driver","jdbc:postgresql://localhost/etribwebed","USER","PASS")
	 * @author bramxy at gmail dot com
	 **/
	public function __construct( $driver, $connectionString, $user, $password ) {
		$this->connectionString = $connectionString;
		$this->user = $user;
		$this->password = $password;

		java('java.lang.Class')->forName( $driver );
		$this->driverManager = new \Java( 'java.sql.DriverManager' );
		$this->getConnection();
	}

	/**
	 * @desc crea la conneccion a la base de datos
	 * @author bramxy at gmail dot com
	 * @example $conn = $connection->getConnection();
	 **/
	public function getConnection() {
		if ( $this->connection == NULL || !$this->connection->isValid(100) ) {
			$this->connection = $this->driverManager->getConnection(
				$this->connectionString, $this->user, $this->password );
		}
		return $this->connection;
	}

	/**
	 * @desc cierra la conneccion a la base de datos
	 * @author cvillaronga at gmail dot com
	 * @example $conn = $connection->getConnection();
	 **/
	public function closeConnection() {
		return $this->connection->close();
	}
}

