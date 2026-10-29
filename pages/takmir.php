<?php
session_start();
include "../config/koneksi.php";
/** @var mysqli $conn */
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];
$masjid_id = $user['masjid_id'];

/* DATA MASJID */
$masjid_q = mysqli_query($conn, "
    SELECT * FROM masjid 
    WHERE id='$masjid_id'
");

$masjid_data = mysqli_fetch_assoc($masjid_q);

$nama_masjid = $masjid_data['nama_masjid'] ?? 'Masjid';
$alamat_masjid = $masjid_data['alamat'] ?? '-';

/* ===================================================
   NOTIFIKASI KHUSUS USER
=================================================== */

/* HITUNG NOTIF BELUM DIBACA */
$count = mysqli_query($conn,"
    SELECT *
    FROM notifikasi_user
    WHERE user_id='$user_id'
    AND status='unread'
");

$total_notif = mysqli_num_rows($count);

/* AMBIL DATA NOTIF */
$notif = mysqli_query($conn,"
    SELECT nu.*, n.judul, n.pesan, n.created_at
    FROM notifikasi_user nu
    JOIN notifikasi n ON nu.notif_id = n.id
    WHERE nu.user_id='$user_id'
    ORDER BY nu.id DESC
    LIMIT 5
");

/* TANDAI DIBACA */
if(isset($_GET['baca'])){

    mysqli_query($conn,"
        UPDATE notifikasi_user
        SET status='read'
        WHERE user_id='$user_id'
    ");

    header("Location: takmir.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Takmir - SIMAKMU</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>

*{
    font-family:'Segoe UI',sans-serif;
    box-sizing:border-box;
}

body{
    min-height:100vh;
    background:linear-gradient(135deg,#2c3e50 0%,#376f80 45%,#4ca1af 100%);
    color:#1f3142;
}

.page-wrapper{
    min-height:100vh;
    padding:24px 34px 40px;
    position:relative;
    overflow-x:hidden;
}

.circle-bg{
    position:absolute;
    border-radius:50%;
    background:rgba(255,255,255,.10);
    z-index:0;
}

.circle-1{
    width:260px;
    height:260px;
    top:-90px;
    right:-80px;
}

.circle-2{
    width:180px;
    height:180px;
    bottom:30px;
    left:-80px;
}

.topbar{
    position:relative;
    z-index:3;
    display:flex;
    justify-content:space-between;
    align-items:center;
    color:white;
    margin-bottom:34px;
}

.brand-area{
    display:flex;
    align-items:center;
    gap:12px;
}

.brand-area img{
    width:52px;
    height:52px;
    object-fit:contain;
}

.brand-title{
    font-size:20px;
    font-weight:800;
    line-height:1.1;
}

.brand-subtitle{
    font-size:13px;
    opacity:.95;
}

.header-right{
    display:flex;
    align-items:center;
    gap:16px;
    font-weight:600;
}

.logout-btn{
    border-radius:12px;
    padding:9px 15px;
    font-weight:700;
}

.dashboard-card{
    background:rgba(255,255,255,.96);
    border:none;
    border-radius:24px;
    box-shadow:0 22px 50px rgba(0,0,0,.18);
    position:relative;
    z-index:2;
}

.section-title{
    font-size:17px;
    font-weight:800;
    color:#20384c;
    display:flex;
    align-items:center;
    gap:8px;
    margin-bottom:16px;
}

.section-title i{
    color:#4ca1af;
}

/* NOTIF */
.notif-wrapper{
    position:relative;
}

.notif-btn{
    width:44px;
    height:44px;
    border:none;
    border-radius:14px;
    background:rgba(255,255,255,.18);
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:20px;
    position:relative;
}

.notif-count{
    position:absolute;
    top:-6px;
    right:-6px;
    background:#dc3545;
    color:white;
    font-size:11px;
    font-weight:800;
    min-width:20px;
    height:20px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
}

.notif-dropdown{
    position:absolute;
    top:55px;
    right:0;
    width:330px;
    background:white;
    color:#20384c;
    border-radius:20px;
    box-shadow:0 20px 50px rgba(0,0,0,.22);
    display:none;
    z-index:99;
    overflow:hidden;
}

.notif-header{
    padding:14px 16px;
    font-weight:800;
    background:#f0f7f9;
    border-bottom:1px solid #dce9ed;
}

.notif-item{
    padding:14px 16px;
    font-size:13px;
    line-height:1.6;
    border-bottom:1px solid #eef3f5;
}

.notif-baru{
    background-color:#d9f2ff;
    border-left:4px solid #4da6ff;
}

.notif-lama{
    background-color:#ffffff;
}


.notif-footer{
    text-align:center;
    padding:12px;
    font-size:13px;
    background:#fff;
    position:sticky;
    bottom:0;
    border-top:1px solid #eef3f5;
}

/* MENU */
.menu-card{
    border-radius:20px;
    padding:20px;
    margin-bottom:14px;
    display:flex;
    align-items:center;
    gap:16px;
    text-decoration:none;
    color:white;
    transition:.25s;
    box-shadow:0 12px 26px rgba(44,62,80,.16);
}

.menu-card:hover{
    transform:translateY(-3px);
    color:white;
    box-shadow:0 18px 36px rgba(44,62,80,.24);
}

.menu-icon{
    min-width:54px;
    width:54px;
    height:54px;
    border-radius:16px;
    background:rgba(255,255,255,.18);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:23px;
}

.menu-title{
    font-size:16px;
    font-weight:800;
    letter-spacing:.2px;
    margin:0;
}

.menu-profile{
    background:linear-gradient(135deg,#2c3e50,#4ca1af);
}

.menu-kegiatan{
    background:linear-gradient(135deg,#00897b,#4db6ac);
}

.menu-jadwal{
    background:linear-gradient(135deg,#1565c0,#42a5f5);
}

/* HERO */
.hero-banner{
    background:linear-gradient(135deg,#2c3e50,#4ca1af);
    border-radius:26px;
    padding:38px;
    color:white;
    overflow:hidden;
    position:relative;
    min-height:100%;
    display:flex;
    align-items:center;
    gap:28px;
}

.hero-banner::before{
    content:'';
    position:absolute;
    width:260px;
    height:260px;
    border-radius:50%;
    background:rgba(255,255,255,.08);
    top:-90px;
    right:-70px;
}

.hero-icon{
    min-width:130px;
    width:130px;
    height:130px;
    border-radius:30px;
    background:rgba(255,255,255,.15);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:66px;
    position:relative;
    z-index:2;
}

.hero-content{
    position:relative;
    z-index:2;
}

.hero-title{
    font-size:34px;
    font-weight:900;
    margin-bottom:12px;
    line-height:1.2;
    text-transform:uppercase;
}

.hero-masjid{
    font-size:18px;
    font-weight:800;
    margin-bottom:8px;
}

.hero-address{
    font-size:15px;
    opacity:.95;
    line-height:1.6;
}

@media(max-width:991px){

    .page-wrapper{
        padding:20px 16px 35px;
    }

    .topbar{
        flex-direction:column;
        align-items:flex-start;
        gap:16px;
    }

    .header-right{
        flex-wrap:wrap;
    }

    .notif-dropdown{
    position:absolute;
    top:55px;
    right:0;
    width:330px;
    background:white;
    color:#20384c;
    border-radius:20px;
    box-shadow:0 20px 50px rgba(0,0,0,.22);
    display:none;
    z-index:9999;

    max-height:420px;
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

    .hero-banner{
        flex-direction:column;
        align-items:flex-start;
    }
}
</style>
</head>

<body>

<div class="page-wrapper">

<div class="circle-bg circle-1"></div>
<div class="circle-bg circle-2"></div>

<!-- TOPBAR -->
<div class="topbar">

    <div class="brand-area">

        <img src="../assets/logo.png">

        <div>
            <div class="brand-title">SIMAKMU</div>

            <div class="brand-subtitle">
                Sistem Informasi Manajemen Kajian Masjid Muhammadiyah
            </div>
        </div>

    </div>

    <div class="header-right">

        <!-- NOTIF -->
        <div class="notif-wrapper">

            <button class="notif-btn" onclick="toggleNotif()">

                <i class="bi bi-bell-fill"></i>

                <?php if($total_notif > 0){ ?>

                    <span class="notif-count">
                        <?= $total_notif; ?>
                    </span>

                <?php } ?>

            </button>

            <div class="notif-dropdown" id="notifBox">

                <div class="notif-header">
                    <i class="bi bi-bell me-1"></i>
                    Notifikasi
                </div>

                <?php if(mysqli_num_rows($notif) > 0){ ?>

                    <?php while($n = mysqli_fetch_assoc($notif)){ ?>

                        <?php
$kelas_notif = ($n['status'] == 'unread') ? 'notif-baru' : 'notif-lama';
?>

<div class="notif-item <?= $kelas_notif; ?>">

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

                    <a href="?baca=1" class="text-decoration-none">
                        Tandai semua telah dibaca
                    </a>

                </div>

            </div>

        </div>

        <div>
            Halo, <b>Takmir <?= htmlspecialchars($nama_masjid); ?></b>
        </div>

        <a href="../auth/logout.php" class="btn btn-danger btn-sm logout-btn">

            <i class="bi bi-box-arrow-right me-1"></i>
            Logout

        </a>

    </div>

</div>

<div class="container-fluid position-relative" style="z-index:2;">

<div class="row g-4 align-items-stretch">

<!-- MENU -->
<div class="col-lg-4">

    <div class="card dashboard-card p-4 h-100">

        <div class="section-title">
            <i class="bi bi-grid"></i>
            Menu Takmir
        </div>

        <a href="profil_takmir.php" class="menu-card menu-profile">

            <div class="menu-icon">
                <i class="bi bi-building"></i>
            </div>

            <div class="menu-title">
                Kelola Profil Masjid
            </div>

        </a>

        <a href="kegiatan_masjid.php" class="menu-card menu-kegiatan">

            <div class="menu-icon">
                <i class="bi bi-calendar2-week"></i>
            </div>

            <div class="menu-title">
                Kegiatan Masjid
            </div>

        </a>

        <a href="ajukan_kajian.php" class="menu-card menu-jadwal">

            <div class="menu-icon">
                <i class="bi bi-calendar-plus"></i>
            </div>

            <div class="menu-title">
                Jadwal Kajian
            </div>

        </a>

    </div>

</div>

<!-- HERO -->
<div class="col-lg-8">

    <div class="card dashboard-card p-4 h-100">

        <div class="hero-banner h-100">

            <div class="hero-icon">
                <i class="bi bi-building-fill"></i>
            </div>

            <div class="hero-content">

                <div class="hero-title">
                    DASHBOARD TAKMIR
                </div>

                <div class="hero-masjid">
                    <?= htmlspecialchars($nama_masjid); ?>
                </div>

                <div class="hero-address">

                    <i class="bi bi-geo-alt-fill me-1"></i>

                    <?= htmlspecialchars($alamat_masjid); ?>

                </div>

            </div>

        </div>

    </div>

</div>

</div>

</div>

</div>

<script>

function toggleNotif(){

    var box = document.getElementById("notifBox");

    box.style.display =
    (box.style.display === "block")
    ? "none"
    : "block";
}

</script>

</body>
</html>