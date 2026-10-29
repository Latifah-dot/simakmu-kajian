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
$masjid_id = $user['masjid_id'];
$today = date('Y-m-d');
$now = date('H:i');

$masjid_q = mysqli_query($conn, "SELECT * FROM masjid WHERE id='$masjid_id'");
$masjid = mysqli_fetch_assoc($masjid_q);
$nama_masjid = $masjid ? $masjid['nama_masjid'] : 'Masjid';

$cekNamaKajian = mysqli_query($conn, "SHOW COLUMNS FROM jadwal LIKE 'nama_kajian'");
$cekJudulKajian = mysqli_query($conn, "SHOW COLUMNS FROM jadwal LIKE 'judul_kajian'");

if (mysqli_num_rows($cekNamaKajian) > 0) {
    $kolom_judul = "nama_kajian";
} elseif (mysqli_num_rows($cekJudulKajian) > 0) {
    $kolom_judul = "judul_kajian";
} else {
    mysqli_query($conn, "ALTER TABLE jadwal ADD nama_kajian VARCHAR(255) AFTER masjid_id");
    $kolom_judul = "nama_kajian";
}

/* =========================
   AJUKAN JADWAL
========================= */

if(isset($_POST['ajukan'])){

    $judul = mysqli_real_escape_string($conn,$_POST['judul_kajian']);
    $tanggal = $_POST['tanggal'];
    $waktu = $_POST['waktu'];

    if ($tanggal < $today) {
        $_SESSION['error'] = "Tanggal tidak boleh sebelum hari ini.";
        header("Location: ajukan_kajian.php");
        exit;
    }

    if ($tanggal == $today && $waktu <= $now) {
        $_SESSION['error'] = "Jam harus lebih maju dari waktu sekarang.";
        header("Location: ajukan_kajian.php");
        exit;
    }

    $cekBentrok = mysqli_query($conn,"
        SELECT *
        FROM kajian_ahad
        WHERE tanggal='$tanggal'
        AND waktu='$waktu'
    ");

    if(mysqli_num_rows($cekBentrok) > 0){

        $_SESSION['error'] = "Jadwal bentrok dengan kajian lain.";

    } else {

        mysqli_query($conn,"
            INSERT INTO jadwal (
                masjid_id,
                $kolom_judul,
                tanggal,
                waktu,
                status
            ) VALUES (
                '$masjid_id',
                '$judul',
                '$tanggal',
                '$waktu',
                'pending'
            )
        ");

        $_SESSION['success'] = "Pengajuan jadwal berhasil dikirim.";
    }

    header("Location: ajukan_kajian.php");
    exit;
}

/* =========================
   HAPUS JADWAL
========================= */

if(isset($_GET['hapus'])){

    $id = $_GET['hapus'];

    mysqli_query($conn,"
        DELETE FROM jadwal
        WHERE id='$id'
        AND masjid_id='$masjid_id'
    ");

    $_SESSION['success'] = "Jadwal berhasil dihapus.";

    header("Location: ajukan_kajian.php");
    exit;
}

/* =========================
   SEARCH + PAGINATION
========================= */

$limit = 5;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if($page < 1){
    $page = 1;
}

$start = ($page - 1) * $limit;

$search = isset($_GET['search'])
    ? mysqli_real_escape_string($conn,$_GET['search'])
    : '';

$where = "WHERE masjid_id='$masjid_id'";

if(!empty($search)){
    $where .= " AND $kolom_judul LIKE '%$search%'";
}

/* TOTAL DATA */

$totalQuery = mysqli_query($conn,"
    SELECT COUNT(*) as total
    FROM jadwal
    $where
");

$totalData = mysqli_fetch_assoc($totalQuery)['total'];

$totalPage = ceil($totalData / $limit);

/* DATA JADWAL */

$jadwal = mysqli_query($conn,"
    SELECT *
    FROM jadwal
    $where
    ORDER BY tanggal DESC, waktu DESC
    LIMIT $start,$limit
");

?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Jadwal Kajian - SIMAKMU</title>
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
    margin-bottom:22px;
}

.section-title i{
    color:#4ca1af;
}

.form-control{
    min-height:50px;
    border-radius:15px;
    border:1px solid #dbe7ec;
    font-size:14px;
}

.form-control:focus{
    border-color:#4ca1af;
    box-shadow:0 0 0 .20rem rgba(76,161,175,.18);
}

.form-label{
    font-size:14px;
    font-weight:700;
    color:#20384c;
}

.btn-main{
    min-height:50px;
    border:none;
    border-radius:15px;
    background:linear-gradient(135deg,#1565c0,#42a5f5);
    color:white;
    font-weight:700;
}

.btn-main:hover{
    color:white;
    transform:translateY(-2px);
}

.table{
    margin-bottom:0;
    font-size:14px;
}

.table thead th{
    background:#f0f7f9;
    color:#20384c;
    font-size:13px;
    font-weight:900;
    text-transform:uppercase;
    text-align:center;
    vertical-align:middle;
}

.table td,
.table th{
    vertical-align:middle;
    padding:14px 12px;
}

.badge-status{
    border-radius:50px;
    padding:7px 12px;
    font-size:12px;
    font-weight:800;
    display:inline-block;
}

.badge-pending{
    background:rgba(255,193,7,.18);
    color:#8a6d00;
}

.badge-acc{
    background:rgba(25,135,84,.14);
    color:#198754;
}

.badge-tolak{
    background:rgba(220,53,69,.14);
    color:#dc3545;
}

.btn-delete{
    width:34px;
    height:34px;
    border-radius:10px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:0;
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

.pagination .page-link{
    border-radius:10px;
    margin:0 3px;
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
            <div class="brand-title">SIMAKMU</div>
            <div class="brand-subtitle">
                Sistem Informasi Manajemen Kajian Masjid Muhammadiyah
            </div>
        </div>

    </div>

    <a href="takmir.php" class="btn btn-light back-btn">
        <i class="bi bi-arrow-left me-1"></i>
        Kembali
    </a>

</div>

<div class="container-fluid position-relative" style="z-index:2;">

<div class="row g-4">

<!-- FORM -->

<div class="col-lg-4">

<div class="card dashboard-card p-4">

<div class="section-title">

    <i class="bi bi-calendar-plus-fill"></i>
    Ajukan Jadwal Kajian

</div>

<form method="POST">

<div class="mb-3">

<label class="form-label">
    Judul Kajian
</label>

<input
    type="text"
    name="judul_kajian"
    class="form-control"
    placeholder="Judul Kajian"
    required
>

</div>

<div class="mb-3">

<label class="form-label">
    Tanggal
</label>

<input
    type="date"
    name="tanggal"
    id="tanggal"
    class="form-control"
    min="<?= $today; ?>"
    required
>

</div>

<div class="mb-4">

<label class="form-label">
    Waktu
</label>

<input
    type="time"
    name="waktu"
    id="waktu"
    class="form-control"
    required
>

</div>

<button type="submit" name="ajukan" class="btn btn-main w-100">

    <i class="bi bi-send me-1"></i>
    Kirim Pengajuan

</button>

</form>

</div>

</div>

<!-- TABEL -->

<div class="col-lg-8">

<div class="card dashboard-card p-4">

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">

<div class="section-title mb-0">

    <i class="bi bi-calendar-event"></i>
    Data Jadwal Kajian <?= htmlspecialchars($nama_masjid); ?>

</div>

</div>

<!-- SEARCH -->

<form method="GET" class="mb-4">

<div class="input-group">

<input
    type="text"
    name="search"
    class="form-control"
    placeholder="Cari judul kajian..."
    value="<?= htmlspecialchars($search); ?>"
>

<button class="btn btn-primary">

    <i class="bi bi-search"></i>

</button>

</div>

</form>

<div class="table-responsive">

<table class="table table-bordered table-hover align-middle">

<thead>

<tr>
    <th width="7%">No</th>
    <th>Judul Kajian</th>
    <th width="17%">Tanggal</th>
    <th width="13%">Waktu</th>
    <th width="18%">Status</th>
    <th width="10%">Aksi</th>
</tr>

</thead>

<tbody>

<?php

$no = $start + 1;

if(mysqli_num_rows($jadwal) > 0){

while($j = mysqli_fetch_assoc($jadwal)){

?>

<tr>

<td class="text-center">
    <?= $no++; ?>
</td>

<td>
    <strong>
        <?= htmlspecialchars($j[$kolom_judul]); ?>
    </strong>
</td>

<td class="text-center">
    <?= date('d-m-Y', strtotime($j['tanggal'])); ?>
</td>

<td class="text-center">
    <?= date('H.i', strtotime($j['waktu'])); ?>
</td>

<td class="text-center">

<?php $status = strtolower($j['status']); ?>

<?php if($status == 'pending' || $status == 'menunggu'){ ?>

<span class="badge-status badge-pending">
    Menunggu
</span>

<?php } elseif($status == 'diterima' || $status == 'disetujui'){ ?>

<span class="badge-status badge-acc">
    Disetujui
</span>

<?php } elseif($status == 'ditolak'){ ?>

<span class="badge-status badge-tolak">
    Ditolak
</span>

<?php } else { ?>

<span class="badge-status badge-pending">
    <?= htmlspecialchars($j['status']); ?>
</span>

<?php } ?>

</td>

<td class="text-center">

<a
    href="?hapus=<?= $j['id']; ?>"
    class="btn btn-danger btn-delete"
    onclick="return confirm('Hapus jadwal ini?')"
>
    <i class="bi bi-trash"></i>
</a>

</td>

</tr>

<?php
    }

} else {
?>

<tr>

<td colspan="6">

<div class="empty-data">

<i class="bi bi-calendar-x"></i>

<div>
    Belum ada pengajuan jadwal.
</div>

</div>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

<!-- PAGINATION -->

<?php if($totalPage > 1){ ?>

<nav class="mt-4">

<ul class="pagination justify-content-center">

<?php if($page > 1){ ?>

<li class="page-item">

<a
    class="page-link"
    href="?page=<?= $page-1; ?>&search=<?= urlencode($search); ?>"
>
    Previous
</a>

</li>

<?php } ?>

<?php for($i=1; $i<=$totalPage; $i++){ ?>

<li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">

<a
    class="page-link"
    href="?page=<?= $i; ?>&search=<?= urlencode($search); ?>"
>
    <?= $i; ?>
</a>

</li>

<?php } ?>

<?php if($page < $totalPage){ ?>

<li class="page-item">

<a
    class="page-link"
    href="?page=<?= $page+1; ?>&search=<?= urlencode($search); ?>"
>
    Next
</a>

</li>

<?php } ?>

</ul>

</nav>

<?php } ?>

</div>

</div>

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
    <?= isset($_SESSION['success']) ? $_SESSION['success'] : ''; ?>
</p>

</div>

</div>

</div>

<!-- ERROR MODAL -->

<div class="modal fade" id="errorModal">

<div class="modal-dialog modal-dialog-centered">

<div class="modal-content">

<div class="error-icon">
    <i class="bi bi-x-lg"></i>
</div>

<h5 class="fw-bold text-danger">
    Peringatan
</h5>

<p class="mb-0">
    <?= isset($_SESSION['error']) ? $_SESSION['error'] : ''; ?>
</p>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>

const tanggalInput = document.getElementById('tanggal');
const waktuInput = document.getElementById('waktu');

const today = "<?= $today; ?>";
const now = "<?= $now; ?>";

function cekTanggalWaktu(){

    if(tanggalInput.value === today){

        waktuInput.min = now;

    } else {

        waktuInput.removeAttribute('min');

    }
}

tanggalInput.addEventListener('change', cekTanggalWaktu);

cekTanggalWaktu();

</script>

<?php if(isset($_SESSION['success'])){ ?>

<script>

var modal = new bootstrap.Modal(document.getElementById('successModal'));

modal.show();

setTimeout(() => {

    modal.hide();

},3000);

</script>

<?php unset($_SESSION['success']); } ?>

<?php if(isset($_SESSION['error'])){ ?>

<script>

var modal = new bootstrap.Modal(document.getElementById('errorModal'));

modal.show();

setTimeout(() => {

    modal.hide();

},3500);

</script>

<?php unset($_SESSION['error']); } ?>

</body>
</html>