<?php
const DB_SERVIDOR = 'serverless-us-west1.sysp0000.db2.skysql.com';
const DB_PUERTO = 4003;
const DB_NOMBRE = 'metro_db';
const DB_USUARIO = 'dbpgf05803082';
const DB_CONTRASENA = 'RXmW|Ta0Bn627u=6tS8iTf';

try {
    $dsn = 'mysql:host=' . DB_SERVIDOR . ';port=' . DB_PUERTO . ';dbname=' . DB_NOMBRE . ';charset=utf8mb4';

    $conexion = new PDO($dsn, DB_USUARIO, DB_CONTRASENA, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        // PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_SSL_CA                 => '',
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ]);
} catch (PDOException $e) {
    die('Error al conectar con la base de datos: ' . $e->getMessage());
}
