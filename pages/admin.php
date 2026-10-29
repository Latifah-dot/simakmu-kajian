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
$today = date('Y-m-d');

function formatTanggalIndo($tanggal) {
    return date('d-m-Y', strtotime($tanggal));
}

function formatJam($waktu) {
    return date('H.i', strtotime($waktu));
}

/* HAPUS KAJIAN */
if (isset($_GET['hapus_kajian'])) {
    $id = $_GET['hapus_kajian'];

    mysqli_query($conn, "
        DELETE FROM kajian_ahad 
        WHERE id='$id'
    ");

    $_SESSION['success'] = "Jadwal kajian berhasil dihapus";
    header("Location: admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Admin - SIMAKMU</title>
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

.admin-info{
    display:flex;
    align-items:center;
    gap:12px;
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

/* MENU */
.menu-card{
    border-radius:20px;
    padding:18px 20px;
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

.menu-masjid{
    background:linear-gradient(135deg,#2c3e50,#4ca1af);
}

.menu-mubaligh{
    background:linear-gradient(135deg,#1565c0,#42a5f5);
}

.menu-kajian{
    background:linear-gradient(135deg,#00897b,#4db6ac);
}

.menu-notif{
    background:linear-gradient(135deg,#f9a825,#ffca28);
    color:#20384c;
}

.menu-notif:hover{
    color:#20384c;
}

/* TABLE */
.table-card{
    overflow:hidden;
}

.table-responsive{
    overflow-x:auto;
}

.table{
    margin-bottom:0;
    font-size:13.5px;
    table-layout:fixed;
}

.table thead th{
    background:#f0f7f9;
    color:#20384c;
    font-size:12.5px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.4px;
    border-bottom:1px solid #d9e8ec;
    text-align:center;
    vertical-align:middle;
}

.table td,
.table th{
    vertical-align:middle;
    padding:13px 10px;
}

.col-judul{
    width:32%;
    white-space:normal !important;
    font-weight:600;
}

.col-tanggal{
    width:13%;
    white-space:nowrap !important;
    text-align:center;
}

.col-jam{
    width:9%;
    white-space:nowrap !important;
    text-align:center;
}

.col-tempat{
    width:20%;
    white-space:normal !important;
}

.col-mubaligh{
    width:18%;
    white-space:normal !important;
}

.col-aksi{
    width:12%;
    white-space:nowrap !important;
    text-align:center;
}

.action-wrapper{
    display:flex;
    justify-content:center;
    align-items:center;
    gap:7px;
}

.btn-action{
    width:34px;
    height:34px;
    border-radius:10px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:0;
    font-size:14px;
}

.btn-edit{
    background:#ffc107;
    color:#20384c;
    border:none;
}

.btn-edit:hover{
    background:#ffca2c;
    color:#20384c;
}

.btn-delete{
    background:#dc3545;
    color:white;
    border:none;
}

.btn-delete:hover{
    background:#bb2d3b;
    color:white;
}

.badge-done{
    border-radius:50px;
    padding:7px 12px;
    font-size:11.5px;
    font-weight:800;
    display:inline-block;
    background:rgba(108,117,125,.14);
    color:#5f6b73;
}

.sub-heading{
    display:flex;
    align-items:center;
    gap:8px;
    padding:12px 16px;
    border-radius:50px;
    font-size:14px;
    font-weight:800;
    margin-bottom:14px;
}

.heading-new{
    background:rgba(76,161,175,.16);
    color:#287485;
}

.heading-done{
    background:rgba(44,62,80,.10);
    color:#2c3e50;
}

.small-note{
    font-size:12px;
    color:#6f8190;
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

@media(max-width:991px){
    .page-wrapper{
        padding:20px 16px 35px;
    }

    .topbar{
        align-items:flex-start;
        gap:16px;
    }

    .admin-info{
        flex-direction:column;
        align-items:flex-end;
        gap:8px;
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
            <div class="brand-subtitle">Sistem Informasi Manajemen Kajian Masjid Muhammadiyah</div>
        </div>
    </div>

    <div class="admin-info">
        <span><i class="bi bi-person-circle me-1"></i> Halo, <?= htmlspecialchars($user['role']); ?></span>

        <a href="../auth/logout.php" class="btn btn-danger btn-sm logout-btn">
            <i class="bi bi-box-arrow-right me-1"></i> Logout
        </a>
    </div>
</div>

<div class="container-fluid position-relative" style="z-index:2;">
<div class="row g-4">

<!-- MENU -->
<div class="col-lg-4">
    <div class="card dashboard-card p-4">

        <div class="section-title">
            <i class="bi bi-grid"></i> Menu Admin
        </div>

        <a href="daftar_masjid.php" class="menu-card menu-masjid">
            <div class="menu-icon">
                <i class="bi bi-building"></i>
            </div>

            <div class="menu-title">
                Daftar Masjid
            </div>
        </a>

        <a href="daftar_mubaligh.php" class="menu-card menu-mubaligh">
            <div class="menu-icon">
                <i class="bi bi-person"></i>
            </div>

            <div class="menu-title">
                Daftar Mubaligh
            </div>
        </a>

        <a href="input_kajian.php" class="menu-card menu-kajian">
            <div class="menu-icon">
                <i class="bi bi-calendar-plus"></i>
            </div>

            <div class="menu-title">
                Input Kajian
            </div>
        </a>

        <a href="kajian_terdekat.php" class="menu-card menu-notif">
            <div class="menu-icon">
                <i class="bi bi-bell"></i>
            </div>

            <div class="menu-title">
                Kajian Terdekat
            </div>
        </a>

    </div>
</div>

<!-- TABEL -->
<div class="col-lg-8">

<div class="card dashboard-card p-4 mb-4 table-card">

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <div class="section-title mb-1">
            <i class="bi bi-calendar-event"></i> Riwayat Kajian
        </div>

        <div class="small-note">
            Jadwal kajian terbaru dan riwayat kajian yang sudah terlaksana.
        </div>
    </div>
</div>

<!-- TERBARU -->
<div class="sub-heading heading-new">
    <i class="bi bi-stars"></i> Jadwal Terbaru
</div>

<div class="table-responsive mb-4">

<table class="table table-bordered table-hover align-middle">

<thead>
<tr>
    <th class="col-judul">Judul Kajian</th>
    <th class="col-tanggal">Tanggal</th>
    <th class="col-jam">Jam</th>
    <th class="col-tempat">Tempat</th>
    <th class="col-mubaligh">Mubaligh</th>
    <th class="col-aksi">Aksi</th>
</tr>
</thead>

<tbody>

<?php
$jadwalBaru = mysqli_query($conn,"
    SELECT k.*,u.nama AS nama_mubaligh
    FROM kajian_ahad k
    LEFT JOIN users u ON k.mubaligh_id=u.id
    WHERE k.tanggal >= '$today'
    ORDER BY k.tanggal ASC,k.waktu ASC
");

if(mysqli_num_rows($jadwalBaru)>0){
    while($d=mysqli_fetch_assoc($jadwalBaru)){
?>

<tr>

<td class="col-judul">
    <?= htmlspecialchars($d['judul']); ?>
</td>

<td class="col-tanggal">
    <?= formatTanggalIndo($d['tanggal']); ?>
</td>

<td class="col-jam">
    <?= formatJam($d['waktu']); ?>
</td>

<td class="col-tempat">
    <?= htmlspecialchars($d['tempat']); ?>
</td>

<td class="col-mubaligh">
    <?= $d['nama_mubaligh'] ? htmlspecialchars($d['nama_mubaligh']) : '-'; ?>
</td>

<td class="col-aksi">

<div class="action-wrapper">

<a href="kajian_terdekat.php?id=<?= $d['id']; ?>" 
class="btn btn-action btn-edit"
title="Edit Kajian">

<i class="bi bi-pencil-square"></i>

</a>

<a href="admin.php?hapus_kajian=<?= $d['id']; ?>" 
class="btn btn-action btn-delete"
title="Hapus Kajian">

<i class="bi bi-trash"></i>

</a>

</div>

</td>

</tr>

<?php
    }
}else{
?>

<tr>
<td colspan="6" class="text-center text-muted">
    Belum ada jadwal terbaru.
</td>
</tr>

<?php } ?>

</tbody>
</table>
</div>

<!-- TERLAKSANA -->
<button class="sub-heading heading-done w-100 border-0 justify-content-between"
type="button"
data-bs-toggle="collapse"
data-bs-target="#riwayatTerlaksana">

<span>
<i class="bi bi-check2-circle"></i> Sudah Terlaksana
</span>

<i class="bi bi-chevron-down"></i>

</button>

<div class="collapse mt-3" id="riwayatTerlaksana">

<?php
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
? mysqli_real_escape_string($conn, $_GET['search']) 
: '';

$whereSearch = "";

if(!empty($search)){
    $whereSearch = "
    AND (
        k.judul LIKE '%$search%' OR
        k.tempat LIKE '%$search%' OR
        u.nama LIKE '%$search%'
    )";
}

/* TOTAL DATA */
$totalQuery = mysqli_query($conn,"
    SELECT COUNT(*) as total
    FROM kajian_ahad k
    LEFT JOIN users u ON k.mubaligh_id=u.id
    WHERE k.tanggal < '$today'
    $whereSearch
");

$totalData = mysqli_fetch_assoc($totalQuery)['total'];

$totalPages = ceil($totalData / $limit);

/* DATA */
$jadwalLama = mysqli_query($conn,"
    SELECT k.*,u.nama AS nama_mubaligh
    FROM kajian_ahad k
    LEFT JOIN users u ON k.mubaligh_id=u.id
    WHERE k.tanggal < '$today'
    $whereSearch
    ORDER BY k.tanggal DESC,k.waktu DESC
    LIMIT $start,$limit
");
?>

<!-- SEARCH -->
<form method="GET" class="mb-3">

<div class="input-group">

<input type="text"
name="search"
class="form-control"
placeholder="Cari judul kajian, tempat, atau mubaligh..."
value="<?= htmlspecialchars($search); ?>">

<button class="btn btn-primary">
<i class="bi bi-search"></i> Cari
</button>

</div>

</form>

<div class="table-responsive">

<table class="table table-bordered table-hover align-middle">

<thead>
<tr>
    <th class="col-judul">Judul Kajian</th>
    <th class="col-tanggal">Tanggal</th>
    <th class="col-jam">Jam</th>
    <th class="col-tempat">Tempat</th>
    <th class="col-mubaligh">Mubaligh</th>
</tr>
</thead>

<tbody>

<?php
if(mysqli_num_rows($jadwalLama)>0){

while($d=mysqli_fetch_assoc($jadwalLama)){
?>

<tr>

<td class="col-judul">
    <?= htmlspecialchars($d['judul']); ?>
</td>

<td class="col-tanggal">
    <?= formatTanggalIndo($d['tanggal']); ?>
</td>

<td class="col-jam">
    <?= formatJam($d['waktu']); ?>
</td>

<td class="col-tempat">
    <?= htmlspecialchars($d['tempat']); ?>
</td>

<td class="col-mubaligh">
    <?= $d['nama_mubaligh'] ? htmlspecialchars($d['nama_mubaligh']) : '-'; ?>
    <br>
    <span class="badge-done">Terlaksana</span>
</td>

</tr>

<?php
}
}else{
?>

<tr>
<td colspan="5" class="text-center text-muted">
    Data kajian tidak ditemukan.
</td>
</tr>

<?php } ?>

</tbody>
</table>

</div>

<!-- PAGINATION -->
<?php if($totalPages > 1){ ?>

<nav class="mt-3">

<ul class="pagination justify-content-center">

<!-- PREV -->
<li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">

<a class="page-link"
href="?page=<?= $page-1; ?>&search=<?= urlencode($search); ?>">

Previous

</a>

</li>

<?php for($i=1; $i <= $totalPages; $i++){ ?>

<li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">

<a class="page-link"
href="?page=<?= $i; ?>&search=<?= urlencode($search); ?>">

<?= $i; ?>

</a>

</li>

<?php } ?>

<!-- NEXT -->
<li class="page-item <?= ($page >= $totalPages) ? 'disabled' : ''; ?>">

<a class="page-link"
href="?page=<?= $page+1; ?>&search=<?= urlencode($search); ?>">

Next

</a>

</li>

</ul>

</nav>

<?php } ?>

</div>

<!-- MODAL -->
<div class="modal fade" id="successModal">

<div class="modal-dialog modal-dialog-centered">

<div class="modal-content">

<div class="success-icon">
    <i class="bi bi-check-lg"></i>
</div>

<h5 class="fw-bold">Berhasil</h5>

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php if(isset($_SESSION['success'])){ ?>

<script>

var modal = new bootstrap.Modal(document.getElementById('successModal'));
modal.show();

setTimeout(() => {
    modal.hide();
},3000);

</script>

<?php unset($_SESSION['success']); } ?>

</body>
</html>