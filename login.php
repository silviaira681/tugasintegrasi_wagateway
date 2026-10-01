<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/fonnte.php";

if (!empty($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {

        $error = "Username dan password wajib diisi.";

    } else {

        try {

            $stmt = $conn->prepare("
                SELECT
                    id,
                    name,
                    username,
                    password,
                    email,
                    no_hp
                FROM users
                WHERE username = ?
                LIMIT 1
            ");

            $stmt->bind_param("s", $username);
            $stmt->execute();

            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if (!$user) {

                $error = "Username atau password salah.";

            } else {

                $passwordBenar = false;

                /*
                |----------------------------------------------------------
                | Cek password hash
                |----------------------------------------------------------
                */
                if (
                    !empty($user['password']) &&
                    password_get_info($user['password'])['algo'] !== 0
                ) {
                    $passwordBenar = password_verify(
                        $password,
                        $user['password']
                    );
                }

                /*
                |----------------------------------------------------------
                | Untuk password awal 123456 yang masih teks biasa
                |----------------------------------------------------------
                */
                if (!$passwordBenar && $password === $user['password']) {
                    $passwordBenar = true;
                }

                if ($passwordBenar) {

                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['nama'] = $user['name'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['no_hp'] = $user['no_hp'] ?? '';
                    $_SESSION['role'] = 'user';

                    /*
                    |------------------------------------------------------
                    | Notifikasi login ke admin
                    |------------------------------------------------------
                    */
                    $nomorAdmin = "6285731637000";

                    $pesanWA =
                        "🔐 LOGIN USER\n\n" .
                        "Nama     : " . $user['name'] . "\n" .
                        "Username : " . $user['username'] . "\n" .
                        "Email    : " . $user['email'] . "\n" .
                        "Waktu    : " . date("d-m-Y H:i:s") . "\n\n" .
                        "User berhasil login ke sistem.";

                    try {
                        kirimWhatsApp($nomorAdmin, $pesanWA);
                    } catch (Throwable $e) {
                        // Login tetap berhasil jika WA gagal.
                    }

                    $_SESSION['success'] =
                        "Selamat datang, " . $user['name'] . "! Anda berhasil login.";

                    header("Location: dashboard.php");
                    exit;

                } else {

                    $error = "Username atau password salah.";
                }
            }

            $stmt->close();

        } catch (Throwable $e) {

            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login User</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #2563eb, #4f46e5);
            padding: 20px;
        }

        .login-box {
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            padding: 35px;
            border-radius: 18px;
            box-shadow: 0 15px 40px rgba(0,0,0,.2);
        }

        .logo {
            width: 70px;
            height: 70px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: #2563eb;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
        }

        h1 {
            text-align: center;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .subtitle {
            text-align: center;
            color: #64748b;
            margin-bottom: 25px;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            color: #334155;
        }

        .form-group {
            margin-bottom: 18px;
        }

        input {
            width: 100%;
            padding: 13px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 15px;
        }

        input:focus {
            outline: none;
            border-color: #2563eb;
        }

        button {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            background: #2563eb;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #1d4ed8;
        }

    </style>

</head>

<body>

<div class="login-box">

    <div class="logo">🔐</div>

    <h1>Login User</h1>

    <p class="subtitle">
        Masuk menggunakan akun siswa
    </p>

    <?php if ($error): ?>

        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>

    <form method="POST">

        <div class="form-group">

            <label>Username</label>

            <input
                type="text"
                name="username"
                placeholder="Masukkan username"
                required
            >

        </div>

        <div class="form-group">

            <label>Password</label>

            <input
                type="password"
                name="password"
                placeholder="Masukkan password"
                required
            >

        </div>

        <button type="submit">
            Login
        </button>

    </form>

</div>

</body>

</html>