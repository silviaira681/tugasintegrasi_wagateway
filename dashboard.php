<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/config.php";

$nama = $_SESSION['nama'] ?? 'User';
$username = $_SESSION['username'] ?? '';
$role = $_SESSION['role'] ?? '';
?>

<?php

// ==========================================
// SESSION
// ==========================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// CEK LOGIN
// ==========================================
// Jika belum login, arahkan ke login.php.
// Jangan arahkan kembali ke dashboard.php.
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// ==========================================
// DATABASE
// ==========================================
require_once __DIR__ . "/config.php";

// ==========================================
// DATA SESSION
// ==========================================
$nama_user = $_SESSION['nama'] ?? $_SESSION['username'] ?? 'Administrator';
$role      = $_SESSION['role'] ?? 'Admin';

// ==========================================
// AMBIL DATA STATISTIK
// ==========================================

$total_user = 0;
$total_admin = 0;
$total_peserta = 0;

// Total semua user
$query_user = $conn->query("SELECT COUNT(*) AS total FROM users");

if ($query_user) {
    $data_user = $query_user->fetch_assoc();
    $total_user = (int) ($data_user['total'] ?? 0);
}

// Total admin
$query_admin = $conn->query(
    "SELECT COUNT(*) AS total FROM users WHERE role = 'admin'"
);

if ($query_admin) {
    $data_admin = $query_admin->fetch_assoc();
    $total_admin = (int) ($data_admin['total'] ?? 0);
}

// Total peserta
$query_peserta = $conn->query(
    "SELECT COUNT(*) AS total FROM users WHERE role != 'admin' OR role IS NULL"
);

if ($query_peserta) {
    $data_peserta = $query_peserta->fetch_assoc();
    $total_peserta = (int) ($data_peserta['total'] ?? 0);
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Dashboard - UJIAN ASTS</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f7fb;
            color: #1f2937;
        }

        /* =====================================
           SIDEBAR
        ===================================== */

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            height: 100vh;
            background: #111827;
            color: white;
            padding: 25px 15px;
        }

        .logo {
            padding: 0 15px 25px;
            border-bottom: 1px solid #374151;
            margin-bottom: 20px;
        }

        .logo h2 {
            font-size: 22px;
        }

        .logo p {
            margin-top: 5px;
            color: #9ca3af;
            font-size: 13px;
        }

        .menu-title {
            padding: 0 15px;
            margin: 18px 0 8px;
            color: #9ca3af;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .menu a {
            display: block;
            padding: 13px 15px;
            margin-bottom: 5px;
            border-radius: 8px;
            color: #d1d5db;
            text-decoration: none;
            transition: 0.2s;
        }

        .menu a:hover,
        .menu a.active {
            background: #2563eb;
            color: white;
        }

        .logout {
            position: absolute;
            left: 15px;
            right: 15px;
            bottom: 20px;
        }

        .logout a {
            display: block;
            text-align: center;
            padding: 12px;
            border-radius: 8px;
            background: #dc2626;
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        .logout a:hover {
            background: #b91c1c;
        }

        /* =====================================
           MAIN
        ===================================== */

        .main {
            margin-left: 250px;
            min-height: 100vh;
        }

        /* =====================================
           TOPBAR
        ===================================== */

        .topbar {
            background: white;
            padding: 18px 30px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .topbar h1 {
            font-size: 24px;
        }

        .profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #2563eb;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .profile-info strong {
            display: block;
            font-size: 14px;
        }

        .profile-info span {
            color: #6b7280;
            font-size: 12px;
        }

        /* =====================================
           CONTENT
        ===================================== */

        .content {
            padding: 30px;
        }

        .welcome {
            background: linear-gradient(
                135deg,
                #2563eb,
                #1d4ed8
            );
            color: white;
            padding: 28px;
            border-radius: 15px;
            margin-bottom: 25px;
        }

        .welcome h2 {
            font-size: 26px;
            margin-bottom: 8px;
        }

        .welcome p {
            color: #dbeafe;
        }

        /* =====================================
           STATISTIC CARDS
        ===================================== */

        .cards {
            display: grid;
            grid-template-columns:
                repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-radius: 14px;
            padding: 22px;
            box-shadow:
                0 5px 20px rgba(0,0,0,0.05);
        }

        .card-icon {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eff6ff;
            font-size: 22px;
            margin-bottom: 15px;
        }

        .card h3 {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .card .number {
            font-size: 30px;
            font-weight: bold;
        }

        /* =====================================
           QUICK MENU
        ===================================== */

        .section-title {
            margin-bottom: 15px;
        }

        .section-title h2 {
            font-size: 20px;
        }

        .section-title p {
            color: #6b7280;
            font-size: 14px;
            margin-top: 4px;
        }

        .quick-menu {
            display: grid;
            grid-template-columns:
                repeat(2, 1fr);
            gap: 18px;
        }

        .quick-card {
            background: white;
            border-radius: 13px;
            padding: 20px;
            text-decoration: none;
            color: #1f2937;
            border: 1px solid #e5e7eb;
            transition: 0.2s;
        }

        .quick-card:hover {
            transform: translateY(-3px);
            box-shadow:
                0 8px 25px rgba(0,0,0,0.08);
            border-color: #2563eb;
        }

        .quick-icon {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .quick-card h3 {
            margin-bottom: 5px;
        }

        .quick-card p {
            color: #6b7280;
            font-size: 13px;
        }

        /* =====================================
           RESPONSIVE
        ===================================== */

        @media (max-width: 900px) {

            .sidebar {
                width: 210px;
            }

            .main {
                margin-left: 210px;
            }

            .cards {
                grid-template-columns:
                    repeat(2, 1fr);
            }

        }

        @media (max-width: 650px) {

            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }

            .main {
                margin-left: 0;
            }

            .logout {
                position: static;
                margin-top: 20px;
            }

            .cards {
                grid-template-columns: 1fr;
            }

            .quick-menu {
                grid-template-columns: 1fr;
            }

            .topbar {
                padding: 15px;
            }

            .content {
                padding: 15px;
            }

        }

    </style>

</head>

<body>

<!-- =========================================
     SIDEBAR
========================================= -->

<aside class="sidebar">

    <div class="logo">

        <h2>UJIAN ASTS</h2>

        <p>Sistem Informasi Peserta</p>

    </div>

    <div class="menu">

        <div class="menu-title">
            Menu Utama
        </div>

        <a href="dashboard.php"
           class="active">
            🏠 Dashboard
        </a>

        <a href="admin_dashboard.php">
            👥 Data User
        </a>

        <a href="tambah_user.php">
            ➕ Tambah User
        </a>

        <a href="daftar_teman.php">
            📋 Daftar User
        </a>

        <div class="menu-title">
            Sistem
        </div>

        <a href="data_json.php">
            📊 Data JSON
        </a>

    </div>

    <div class="logout">

        <a href="logout.php">
            🚪 Logout
        </a>

    </div>

</aside>


<!-- =========================================
     MAIN
========================================= -->

<main class="main">

    <!-- TOPBAR -->

    <header class="topbar">

        <h1>Dashboard</h1>

        <div class="profile">

            <div class="avatar">
                <?= strtoupper(substr($nama_user, 0, 1)) ?>
            </div>

            <div class="profile-info">

                <strong>
                    <?= htmlspecialchars($nama_user) ?>
                </strong>

                <span>
                    <?= htmlspecialchars($role) ?>
                </span>

            </div>

        </div>

    </header>


    <!-- CONTENT -->

    <section class="content">

        <!-- WELCOME -->

        <div class="welcome">

            <h2>
                Selamat Datang, <?= htmlspecialchars($nama_user) ?> 👋
            </h2>

            <p>
                Kelola data peserta dan sistem UJIAN ASTS
                melalui dashboard ini.
            </p>

        </div>


        <!-- STATISTICS -->

        <div class="cards">

            <div class="card">

                <div class="card-icon">
                    👥
                </div>

                <h3>
                    Total User
                </h3>

                <div class="number">
                    <?= $total_user ?>
                </div>

            </div>


            <div class="card">

                <div class="card-icon">
                    👨‍💼
                </div>

                <h3>
                    Total Admin
                </h3>

                <div class="number">
                    <?= $total_admin ?>
                </div>

            </div>


            <div class="card">

                <div class="card-icon">
                    🎓
                </div>

                <h3>
                    Total Peserta
                </h3>

                <div class="number">
                    <?= $total_peserta ?>
                </div>

            </div>

        </div>


        <!-- QUICK MENU -->

        <div class="section-title">

            <h2>
                Menu Cepat
            </h2>

            <p>
                Pilih menu yang ingin kamu gunakan.
            </p>

        </div>


        <div class="quick-menu">

            <a href="admin_dashboard.php"
               class="quick-card">

                <div class="quick-icon">
                    👥
                </div>

                <h3>
                    Data User
                </h3>

                <p>
                    Melihat dan mengelola data peserta.
                </p>

            </a>


            <a href="tambah_user.php"
               class="quick-card">

                <div class="quick-icon">
                    ➕
                </div>

                <h3>
                    Tambah User
                </h3>

                <p>
                    Menambahkan peserta baru dan
                    mengirim notifikasi WhatsApp.
                </p>

            </a>


            <a href="daftar_teman.php"
               class="quick-card">

                <div class="quick-icon">
                    📋
                </div>

                <h3>
                    Daftar User
                </h3>

                <p>
                    Melihat daftar user yang telah
                    terdaftar.
                </p>

            </a>


            <a href="data_json.php"
               class="quick-card">

                <div class="quick-icon">
                    📊
                </div>

                <h3>
                    Data JSON
                </h3>

                <p>
                    Melihat data sistem dalam format JSON.
                </p>

            </a>

        </div>

    </section>

</main>

</body>

</html>