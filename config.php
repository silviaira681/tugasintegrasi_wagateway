<?php

mysqli_report(MYSQLI_REPORT_OFF);


/*
|--------------------------------------------------------------------------
| DATABASE
|--------------------------------------------------------------------------
*/

$host = "localhost";

$user = "root";

$pass = "";

$db = "ujian_asts";


$conn = new mysqli(
    $host,
    $user,
    $pass,
    $db
);


if ($conn->connect_error) {

    die(
        "Koneksi database gagal: " .
        $conn->connect_error
    );
}


$conn->set_charset("utf8mb4");


/*
|--------------------------------------------------------------------------
| TABEL AUTH USERS
|--------------------------------------------------------------------------
*/

$conn->query("
    CREATE TABLE IF NOT EXISTS auth_users (

        id INT AUTO_INCREMENT PRIMARY KEY,

        nama VARCHAR(100) NOT NULL,

        username VARCHAR(50) NOT NULL UNIQUE,

        no_telepon VARCHAR(20) DEFAULT NULL,

        password VARCHAR(255) NOT NULL,

        role VARCHAR(30) NOT NULL DEFAULT 'User',

        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

    ) ENGINE=InnoDB
      DEFAULT CHARSET=utf8mb4
");


/*
|--------------------------------------------------------------------------
| PASTIKAN KOLOM NO TELEPON ADA
|--------------------------------------------------------------------------
*/

$cekKolom = $conn->query("
    SHOW COLUMNS
    FROM auth_users
    LIKE 'no_telepon'
");


if (
    $cekKolom &&
    $cekKolom->num_rows === 0
) {

    $conn->query("
        ALTER TABLE auth_users

        ADD COLUMN
        no_telepon VARCHAR(20) NULL

        AFTER username
    ");
}


/*
|--------------------------------------------------------------------------
| TABEL NOTIFIKASI ADMIN
|--------------------------------------------------------------------------
*/

$conn->query("
    CREATE TABLE IF NOT EXISTS admin_notifications (

        id INT AUTO_INCREMENT PRIMARY KEY,

        user_id INT NULL,

        title VARCHAR(150) NOT NULL,

        message TEXT NOT NULL,

        is_read TINYINT(1) NOT NULL DEFAULT 0,

        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

    ) ENGINE=InnoDB
      DEFAULT CHARSET=utf8mb4
");


/*
|--------------------------------------------------------------------------
| ADMIN DEFAULT
|--------------------------------------------------------------------------
|
| Username : admin
| Password : admin123
|
|--------------------------------------------------------------------------
*/

$cekAdmin = $conn->query("
    SELECT id
    FROM auth_users
    WHERE username = 'admin'
    LIMIT 1
");


if (
    $cekAdmin &&
    $cekAdmin->num_rows === 0
) {

    $namaAdmin =
        "Administrator";

    $usernameAdmin =
        "admin";

    $passwordAdmin =
        password_hash(
            "admin123",
            PASSWORD_DEFAULT
        );

    $roleAdmin =
        "Admin";


    $stmtAdmin = $conn->prepare("
        INSERT INTO auth_users
        (
            nama,
            username,
            password,
            role
        )
        VALUES
        (?, ?, ?, ?)
    ");


    $stmtAdmin->bind_param(
        "ssss",
        $namaAdmin,
        $usernameAdmin,
        $passwordAdmin,
        $roleAdmin
    );


    $stmtAdmin->execute();

    $stmtAdmin->close();
}

?>