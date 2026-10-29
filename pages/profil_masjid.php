<?php
session_start();
include "../config/koneksi.php";
/** @var mysqli $conn */
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: daftar_masjid.php");
    exit;
}

$id = $_GET['id'];

/* =========================
   DATA MASJID
========================= */

$masjid = mysqli_query($conn,"
    SELECT *
    FROM masjid
    WHERE id='$id'
");

$dataMasjid = mysqli_fetch_assoc($masjid);

if (!$dataMasjid) {
    header("Location: daftar_masjid.php");
    exit;
}

/* =========================
   DATA KEGIATAN
========================= */

$kegiatan = mysqli_query($conn,"
    SELECT *
    FROM kegiatan
    WHERE masjid_id='$id'
    ORDER BY tanggal DESC
");

?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Profil Masjid - SIMAKMU</title>
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

.back-btn{
    border-radius:12px;
    padding:10px 16px;
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
    font-size:18px;
    font-weight:800;
    color:#20384c;
    display:flex;
    align-items:center;
    gap:8px;
    margin-bottom:20px;
}

.section-title i{
    color:#4ca1af;
}

/* PROFIL MASJID */

.masjid-banner{
    background:linear-gradient(135deg,#2c3e50,#4ca1af);
    border-radius:26px;
    padding:34px;
    color:white;
    overflow:hidden;
    position:relative;
}

.masjid-banner::before{
    content:'';
    position:absolute;
    width:250px;
    height:250px;
    border-radius:50%;
    background:rgba(255,255,255,.08);
    top:-90px;
    right:-70px;
}

.masjid-icon{
    width:92px;
    height:92px;
    border-radius:26px;
    background:rgba(255,255,255,.14);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:45px;
    margin-bottom:20px;
    backdrop-filter:blur(5px);
}

.masjid-name{
    font-size:30px;
    font-weight:800;
    margin-bottom:8px;
}

.masjid-address{
    font-size:15px;
    opacity:.95;
}

/* TABLE */

.table{
    margin-bottom:0;
}

.table thead th{
    background:#f0f7f9;
    color:#20384c;
    border-bottom:1px solid #dce9ed;
    font-size:13px;
}

.table td,
.table th{
    vertical-align:middle;
    padding:14px 12px;
}

.badge-kegiatan{
    background:rgba(76,161,175,.15);
    color:#287485;
    border-radius:50px;
    padding:7px 12px;
    font-size:12px;
    font-weight:700;
}

.empty-data{
    text-align:center;
    padding:45px 10px;
    color:#7c8d99;
}

.empty-data i{
    font-size:52px;
    margin-bottom:12px;
    color:#b8c7cf;
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

    <a href="daftar_masjid.php" class="btn btn-light back-btn">

        <i class="bi bi-arrow-left me-1"></i>
        Kembali

    </a>

</div>

<div class="container-fluid position-relative" style="z-index:2;">

<!-- PROFIL MASJID -->

<div class="card dashboard-card p-4 mb-4">

    <div class="masjid-banner">

        <div class="masjid-icon">
            <i class="bi bi-building-fill"></i>
        </div>

        <div class="masjid-name">
            <?= htmlspecialchars($dataMasjid['nama_masjid']); ?>
        </div>

        <div class="masjid-address">
            <i class="bi bi-geo-alt-fill me-1"></i>
            <?= htmlspecialchars($dataMasjid['alamat']); ?>
        </div>

    </div>

</div>

<!-- KEGIATAN MASJID -->

<div class="card dashboard-card p-4">

    <div class="section-title">

        <i class="bi bi-calendar2-week"></i>
        Kegiatan Masjid

    </div>

    <div class="table-responsive">

        <table class="table table-bordered table-hover align-middle">

            <thead>

                <tr>
                    <th width="8%">NO</th>
                    <th>NAMA KEGIATAN</th>
                    <th>TANGGAL</th>
                    <th>WAKTU</th>
                    <th width="15%">STATUS</th>
                </tr>

            </thead>

            <tbody>

                <?php

                $no = 1;

                if(mysqli_num_rows($kegiatan)>0){

                    while($k = mysqli_fetch_assoc($kegiatan)){

                ?>

                <tr>

                    <td><?= $no++; ?></td>

                    <td>
                        <?= htmlspecialchars($k['nama_kegiatan']); ?>
                    </td>

                    <td>
                        <?= date('d-m-Y', strtotime($k['tanggal'])); ?>
                    </td>

                    <td>
                        <?= date('H.i', strtotime($k['waktu'])); ?>
                    </td>

                    <td>

    <?php
    $tanggalKegiatan = $k['tanggal'];

    if($tanggalKegiatan < date('Y-m-d')){
    ?>

        <span class="badge-kegiatan" style="
            background: rgba(108,117,125,.15);
            color:#5f6b73;
        ">
            Terlaksana
        </span>

    <?php } else { ?>

        <span class="badge-kegiatan">
            Terjadwal
        </span>

    <?php } ?>

</td>

                </tr>

                <?php

                    }

                } else {

                ?>

                <tr>

                    <td colspan="5">

                        <div class="empty-data">

                            <i class="bi bi-calendar-x"></i>

                            <div>
                                Belum ada kegiatan masjid.
                            </div>

                        </div>

                    </td>

                </tr>

                <?php } ?>

            </tbody>

        </table>

    </div>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>