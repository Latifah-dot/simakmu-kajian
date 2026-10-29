<?php
session_start();
include "../config/koneksi.php";
/** @var mysqli $conn */

if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit;
}

date_default_timezone_set('Asia/Jakarta');

$user = $_SESSION['user'];
$mubaligh_id = $user['id'];

$today = date('Y-m-d');
$now = date('H:i:s');

/* ======================================================
   TOTAL KONFIRMASI
====================================================== */

$totalKonfirmasi = mysqli_num_rows(mysqli_query($conn,"
    SELECT *
    FROM jadwal
    WHERE status='pending'
"));

/* ======================================================
   TOTAL JADWAL ANDA
====================================================== */

$totalJadwalSaya = mysqli_num_rows(mysqli_query($conn,"
    SELECT *
    FROM jadwal
    WHERE mubaligh_id='$mubaligh_id'
    AND (
        tanggal > '$today'
        OR (
            tanggal='$today'
            AND waktu >= '$now'
        )
    )
"));

/* ======================================================
   TOTAL RIWAYAT
====================================================== */

$totalRiwayat = mysqli_num_rows(mysqli_query($conn,"
    SELECT *
    FROM jadwal
    WHERE mubaligh_id='$mubaligh_id'
    AND (
        tanggal < '$today'
        OR (
            tanggal='$today'
            AND waktu < '$now'
        )
    )
"));

/* ======================================================
   TOTAL NOTIFIKASI
====================================================== */

$countNotif = mysqli_query($conn,"
    SELECT COUNT(*) as total
    FROM notifikasi_user
    WHERE user_id='$mubaligh_id'
    AND status='unread'
");

$dataNotif = mysqli_fetch_assoc($countNotif);

$total_notif = $dataNotif['total'];

/* ======================================================
   DATA NOTIFIKASI
====================================================== */

$notif = mysqli_query($conn,"
    SELECT n.*, nu.status
    FROM notifikasi_user nu
    JOIN notifikasi n ON nu.notif_id = n.id
    WHERE nu.user_id='$mubaligh_id'
    ORDER BY nu.id DESC
    LIMIT 5
");

/* ======================================================
   TANDAI DIBACA
====================================================== */

if(isset($_GET['baca'])){

    mysqli_query($conn,"
        UPDATE notifikasi_user
        SET status='read'
        WHERE user_id='$mubaligh_id'
    ");

    header("Location: mubaligh.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Mubaligh - SIMAKMU</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>

*{
    font-family:'Segoe UI',sans-serif;
    box-sizing:border-box;
}

body{
    margin:0;
    min-height:100vh;
    background:
    linear-gradient(
        135deg,
        #2c3e50 0%,
        #376f80 45%,
        #4ca1af 100%
    );
}

.wrapper{
    min-height:100vh;
    padding:30px;
    position:relative;
    overflow:hidden;
}

.circle{
    position:absolute;
    border-radius:50%;
    background:rgba(255,255,255,.08);
}

.circle1{
    width:260px;
    height:260px;
    top:-80px;
    right:-80px;
}

.circle2{
    width:180px;
    height:180px;
    bottom:-60px;
    left:-60px;
}

.topbar{
    position:relative;
    z-index:1000;
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:35px;
}

.brand{
    display:flex;
    align-items:center;
    gap:14px;
    color:white;
}

.brand img{
    width:58px;
}

.brand h2{
    margin:0;
    font-size:22px;
    font-weight:800;
}

.brand p{
    margin:0;
    font-size:13px;
    opacity:.9;
}

.header-right{
    display:flex;
    align-items:center;
    gap:15px;
}

/* =========================================
   NOTIFIKASI
========================================= */

.notif-wrapper{
    position:relative;
    z-index:9999;
}

.notif-btn{
    width:46px;
    height:46px;
    border:none;
    border-radius:14px;
    background:rgba(255,255,255,.18);
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:21px;
    position:relative;
}

.notif-btn:hover{
    background:rgba(255,255,255,.28);
}

.notif-count{
    position:absolute;
    top:-5px;
    right:-5px;
    background:#dc3545;
    color:white;
    min-width:22px;
    height:22px;
    border-radius:50%;
    display:flex !important;
    align-items:center;
    justify-content:center;
    font-size:11px;
    font-weight:800;
    padding:0 6px;
    z-index:999999;
}

.notif-dropdown{
    position:absolute;
    top:58px;
    right:0;
    width:340px;
    background:white;
    border-radius:22px;
    box-shadow:0 20px 50px rgba(0,0,0,.28);
    display:none;
    z-index:99999;

    max-height:450px;
    overflow-y:auto;
}

.notif-header{
    padding:16px;
    background:#f3f7fa;
    font-weight:800;
    color:#20384c;
    border-bottom:1px solid #e8edf1;
}

.notif-item{
    padding:14px 16px;
    border-bottom:1px solid #edf2f5;
    font-size:13px;
    color:#4c5d6b;
    line-height:1.6;
    background:white;
    transition:.2s;
}

.notif-unread{
    background:#e8f4ff;
}

.notif-item:last-child{
    border-bottom:none;
}

.notif-footer{
    padding:12px;
    text-align:center;
    background:#fafcfd;
}

.notif-footer a{
    text-decoration:none;
    font-size:13px;
    font-weight:700;
    color:#0d6efd;
}

/* =========================================
   LOGOUT
========================================= */

.logout-btn{
    background:#dc3545;
    color:white;
    border:none;
    border-radius:14px;
    padding:11px 18px;
    font-weight:700;
    text-decoration:none;
    transition:.2s;
}

.logout-btn:hover{
    background:#bb2d3b;
    color:white;
    transform:translateY(-2px);
}

/* =========================================
   WELCOME
========================================= */

.welcome-card{
    position:relative;
    z-index:2;
    background:rgba(255,255,255,.95);
    border-radius:28px;
    padding:35px;
    box-shadow:0 20px 45px rgba(0,0,0,.15);
    margin-bottom:28px;
}

.welcome-title{
    font-size:17px;
    font-weight:700;
    color:#4f6270;
    margin-bottom:8px;
}

.nama-mubaligh{
    font-size:28px;
    font-weight:800;
    color:#0d3b66;
    margin-bottom:12px;
}

.welcome-subtitle{
    color:#6c7d8a;
    font-size:15px;
    line-height:1.7;
}

/* =========================================
   MENU
========================================= */

.menu-grid{
    position:relative;
    z-index:2;
}

.menu-card{
    position:relative;
    overflow:hidden;
    background:rgba(255,255,255,.96);
    border-radius:26px;
    padding:28px;
    text-decoration:none;
    display:block;
    box-shadow:0 18px 35px rgba(0,0,0,.15);
    transition:.25s;
    height:100%;
}

.menu-card:hover{
    transform:translateY(-6px);
}

.menu-icon{
    width:72px;
    height:72px;
    border-radius:22px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:34px;
    margin-bottom:18px;
}

.icon-blue{
    background:rgba(13,110,253,.12);
    color:#0d6efd;
}

.icon-green{
    background:rgba(25,135,84,.12);
    color:#198754;
}

.icon-dark{
    background:rgba(108,117,125,.14);
    color:#495057;
}

.menu-title{
    font-size:20px;
    font-weight:800;
    color:#20384c;
    margin-bottom:10px;
}

.menu-desc{
    color:#6d7d8b;
    font-size:14px;
    line-height:1.6;
}

.badge-total{
    position:absolute;
    top:18px;
    right:18px;
    min-width:38px;
    height:38px;
    border-radius:50px;
    background:#dc3545;
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:14px;
    font-weight:800;
    padding:0 12px;
}

@media(max-width:991px){

    .wrapper{
        padding:20px;
    }

    .topbar{
        flex-direction:column;
        align-items:flex-start;
        gap:16px;
    }

    .notif-dropdown{
        right:auto;
        left:0;
        width:300px;
    }

    .nama-mubaligh{
        font-size:24px;
    }
}

</style>
</head>

<body>

<div class="wrapper">

<div class="circle circle1"></div>
<div class="circle circle2"></div>

<!-- TOPBAR -->

<div class="topbar">

    <div class="brand">

        <img src="../assets/logo.png">

        <div>

            <h2>SIMAKMU</h2>

            <p>
                Sistem Informasi Manajemen Kajian
                Masjid Muhammadiyah
            </p>

        </div>

    </div>

    <div class="header-right">

        <!-- NOTIFIKASI -->

        <div class="notif-wrapper">

            <button class="notif-btn" onclick="toggleNotif()">

                <i class="bi bi-bell-fill"></i>

                <?php if((int)$total_notif > 0){ ?>

    <span class="notif-count">

        <?= (int)$total_notif; ?>

    </span>

<?php } ?>

            </button>

            <div class="notif-dropdown" id="notifBox">

                <div class="notif-header">

                    <i class="bi bi-bell me-1"></i>
                    Notifikasi

                </div>

                <?php if($notif && mysqli_num_rows($notif) > 0){ ?>

                    <?php while($n = mysqli_fetch_assoc($notif)){ ?>

                        <div class="notif-item <?= $n['status'] == 'unread' ? 'notif-unread' : ''; ?>">

                            <div class="fw-bold mb-1">
                                <?= htmlspecialchars($n['judul']); ?>
                            </div>

                            <?= htmlspecialchars($n['pesan']); ?>

                            <div class="text-muted mt-1" style="font-size:11px;">
                                <?= date('d-m-Y H:i', strtotime($n['created_at'])); ?>
                            </div>

                        </div>

                    <?php } ?>

                <?php } else { ?>

                    <div class="notif-item text-muted">

                        Belum ada notifikasi.

                    </div>

                <?php } ?>

                <div class="notif-footer">

                    <a href="?baca=1">

                        Tandai semua telah dibaca

                    </a>

                </div>

            </div>

        </div>

        <!-- LOGOUT -->

        <a href="../auth/logout.php" class="logout-btn">

            <i class="bi bi-box-arrow-right me-1"></i>
            Logout

        </a>

    </div>

</div>

<!-- WELCOME -->

<div class="welcome-card">

    <div class="welcome-title">

        Assalamu'alaikum,

    </div>

    <div class="nama-mubaligh">

        <?= htmlspecialchars($user['nama']); ?>

    </div>

    <div class="welcome-subtitle">

        Selamat datang di dashboard mubaligh.
        Silakan kelola jadwal kajian Anda melalui menu berikut.

    </div>

</div>

<!-- MENU -->

<div class="row g-4 menu-grid">

    <!-- KONFIRMASI -->

    <div class="col-lg-4">

       <a href="konfirmasi_jadwal.php" class="menu-card">

    <div class="menu-icon icon-blue">

                <i class="bi bi-calendar-check-fill"></i>

            </div>

            <div class="menu-title">

                Konfirmasi Jadwal

            </div>

            <div class="menu-desc">

                Lihat pengajuan jadwal kajian dari takmir
                dan ambil jadwal yang tersedia.

            </div>

        </a>

    </div>

    <!-- JADWAL ANDA -->

    <div class="col-lg-4">

        <a href="jadwal_anda.php" class="menu-card">

            <?php if($totalJadwalSaya > 0){ ?>

                <div class="badge-total">

                    <?= $totalJadwalSaya; ?>

                </div>

            <?php } ?>

            <div class="menu-icon icon-green">

                <i class="bi bi-journal-check"></i>

            </div>

            <div class="menu-title">

                Jadwal Anda

            </div>

            <div class="menu-desc">

                Semua jadwal kajian yang sedang
                Anda isi akan tampil di menu ini.

            </div>

        </a>

    </div>

    <!-- RIWAYAT -->

    <div class="col-lg-4">

        <a href="riwayat_jadwal.php" class="menu-card">

    <div class="menu-icon icon-dark">

                <i class="bi bi-clock-history"></i>

            </div>

            <div class="menu-title">

                Riwayat Jadwal

            </div>

            <div class="menu-desc">

                Riwayat kajian yang sudah selesai
                atau telah terlaksana.

            </div>

        </a>

    </div>

</div>

</div>

<script>

function toggleNotif(){

    var box = document.getElementById("notifBox");

    if(box.style.display === "block"){
        box.style.display = "none";
    }else{
        box.style.display = "block";
    }
}

</script>

</body>
</html>