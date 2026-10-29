<?php
session_start();
include "../config/koneksi.php";
/** @var mysqli $conn */
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

/* =========================================
   CEK KOLOM MUBALIGH_ID
========================================= */

$cekKolom = mysqli_query($conn, "
    SHOW COLUMNS FROM kajian_ahad 
    LIKE 'mubaligh_id'
");

if (mysqli_num_rows($cekKolom) == 0) {

    mysqli_query($conn, "
        ALTER TABLE kajian_ahad
        ADD mubaligh_id INT(11) NULL
        AFTER tempat
    ");
}

/* =========================================
   SIMPAN KAJIAN
========================================= */

if(isset($_POST['simpan'])){

    $judul       = mysqli_real_escape_string($conn,$_POST['judul']);
    $tanggal     = $_POST['tanggal'];
    $waktu       = $_POST['waktu'];
    $tempat      = mysqli_real_escape_string($conn,$_POST['tempat']);
    $mubaligh_id = $_POST['mubaligh_id'];

    /* =========================================
       CEK BENTROK
    ========================================= */

    $cekBentrok = mysqli_query($conn,"
        SELECT k.*, u.nama AS nama_mubaligh
        FROM kajian_ahad k
        LEFT JOIN users u
        ON k.mubaligh_id = u.id
        WHERE k.tanggal='$tanggal'
        AND k.waktu='$waktu'
        AND (
            k.tempat='$tempat'
            OR k.mubaligh_id='$mubaligh_id'
        )
        LIMIT 1
    ");

    if(mysqli_num_rows($cekBentrok) > 0){

        $bentrok = mysqli_fetch_assoc($cekBentrok);

        $_SESSION['error'] =
        "Jadwal sudah terisi atau bentrok dengan kajian: "
        . $bentrok['judul']
        . " pada tanggal "
        . date('d-m-Y', strtotime($bentrok['tanggal']))
        . " pukul "
        . date('H.i', strtotime($bentrok['waktu']))
        . " di "
        . $bentrok['tempat']
        . " oleh "
        . (
            $bentrok['nama_mubaligh']
            ? $bentrok['nama_mubaligh']
            : '-'
        );

        header("Location: input_kajian.php");
        exit;

    } else {

        /* =========================================
           SIMPAN KAJIAN
        ========================================= */

        mysqli_query($conn,"
    INSERT INTO kajian_ahad
    (
        judul,
        tanggal,
        waktu,
        tempat,
        mubaligh_id,
        status
    )
    VALUES
    (
        '$judul',
        '$tanggal',
        '$waktu',
        '$tempat',
        '$mubaligh_id',
        'menunggu'
    )
");


$_SESSION['success'] = "Jadwal kajian berhasil disimpan";
header("Location: input_kajian.php");
exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">

<title>
    Input Kajian - SIMAKMU
</title>

<meta name="viewport" content="width=device-width, initial-scale=1">

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
rel="stylesheet">

<style>

*{
    font-family:'Segoe UI',sans-serif;
    box-sizing:border-box;
}

body{
    min-height:100vh;
    background:
    linear-gradient(
        135deg,
        #2c3e50 0%,
        #376f80 45%,
        #4ca1af 100%
    );

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
    margin-bottom:24px;
}

.section-title i{
    color:#4ca1af;
}

.form-control,
.form-select{
    min-height:52px;
    border-radius:15px;
    border:1px solid #dbe7ec;
    font-size:14px;
}

.form-control:focus,
.form-select:focus{
    border-color:#4ca1af;
    box-shadow:0 0 0 .20rem rgba(76,161,175,.18);
}

.btn-main{
    min-height:52px;
    border:none;
    border-radius:15px;
    background:
    linear-gradient(
        135deg,
        #00897b,
        #4db6ac
    );

    color:white;
    font-weight:700;
}

.btn-main:hover{
    color:white;
    transform:translateY(-2px);
}

.form-card{
    max-width:620px;
    margin:auto;
}

.modal-content{
    border:none;
    border-radius:24px;
    padding:28px;
    text-align:center;
}

.success-icon{
    width:72px;
    height:72px;
    border-radius:50%;
    background:rgba(25,135,84,.13);
    color:#198754;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:38px;
    margin:0 auto 14px;
}

.error-icon{
    width:72px;
    height:72px;
    border-radius:50%;
    background:rgba(220,53,69,.13);
    color:#dc3545;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:38px;
    margin:0 auto 14px;
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

<div class="topbar">

    <div class="brand-area">

        <img src="../assets/logo.png">

        <div>

            <div class="brand-title">
                SIMAKMU
            </div>

            <div class="brand-subtitle">
                Sistem Informasi Manajemen Kajian
                Masjid Muhammadiyah
            </div>

        </div>

    </div>

    <a href="admin.php" class="btn btn-light back-btn">

        <i class="bi bi-arrow-left me-1"></i>

        Kembali

    </a>

</div>

<div class="container-fluid position-relative" style="z-index:2;">

<div class="card dashboard-card p-4 form-card">

<div class="section-title">

    <i class="bi bi-calendar-plus-fill"></i>

    Input Kajian

</div>

<form method="POST">

<div class="mb-3">

<input
type="text"
name="judul"
class="form-control"
placeholder="Tema / Judul Kajian"
required>

</div>

<div class="mb-3">

<input
type="date"
name="tanggal"
class="form-control"
min="<?= date('Y-m-d'); ?>"
required>

</div>

<div class="mb-3">

<input
type="time"
name="waktu"
class="form-control"
required>

</div>

<div class="mb-3">

<input
type="text"
name="tempat"
class="form-control"
placeholder="Tempat Kajian"
required>

</div>

<div class="mb-4">

<select
name="mubaligh_id"
class="form-select"
required>

<option value="">
    Pilih Mubaligh
</option>

<?php
$mubaligh = mysqli_query($conn,"
    SELECT *
    FROM users
    WHERE role='mubaligh'
    ORDER BY nama ASC
");

while($m = mysqli_fetch_assoc($mubaligh)){
?>

<option value="<?= $m['id']; ?>">

    <?= htmlspecialchars($m['nama']); ?>

</option>

<?php } ?>

</select>

</div>

<button
type="submit"
name="simpan"
class="btn btn-main w-100">

<i class="bi bi-save me-1"></i>

Simpan Kajian

</button>

</form>

</div>

</div>

</div>

<!-- SUCCESS MODAL -->

<div class="modal fade" id="successModal">

<div class="modal-dialog modal-dialog-centered">

<div class="modal-content">

<div class="success-icon">

<i class="bi bi-check-lg"></i>

</div>

<h5 class="fw-bold">

Berhasil

</h5>

<p class="mb-0">

<?php
if(isset($_SESSION['success'])){
    echo $_SESSION['success'];
}
?>

</p>

</div>

</div>

</div>

<!-- ERROR MODAL -->

<div class="modal fade" id="errorModal">

<div class="modal-dialog modal-dialog-centered">

<div class="modal-content">

<div class="error-icon">

<i class="bi bi-exclamation-triangle-fill"></i>

</div>

<h5 class="fw-bold">

Jadwal Bentrok

</h5>

<p class="mb-0">

<?php
if(isset($_SESSION['error'])){
    echo $_SESSION['error'];
}
?>

</p>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php if(isset($_SESSION['success'])){ ?>

<script>

var modal =
new bootstrap.Modal(
document.getElementById('successModal')
);

modal.show();

setTimeout(() => {

    modal.hide();

},3000);

</script>

<?php unset($_SESSION['success']); } ?>

<?php if(isset($_SESSION['error'])){ ?>

<script>

var modal =
new bootstrap.Modal(
document.getElementById('errorModal')
);

modal.show();

</script>

<?php unset($_SESSION['error']); } ?>

</body>
</html>