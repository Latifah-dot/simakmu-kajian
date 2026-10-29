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

/* =========================
   PAGINATION + SEARCH
========================= */

$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($page < 1) {
    $page = 1;
}

$start = ($page - 1) * $limit;

$search = isset($_GET['search']) 
    ? mysqli_real_escape_string($conn, $_GET['search']) 
    : '';

$whereSearch = "";

if (!empty($search)) {

    $whereSearch = "
        AND (
            nama_kegiatan LIKE '%$search%'
            OR tanggal LIKE '%$search%'
        )
    ";
}

/* =========================
   CEK KOLOM DOKUMENTASI
========================= */

$cekKolom = mysqli_query($conn, "
    SHOW COLUMNS FROM kegiatan LIKE 'dokumentasi'
");

if (mysqli_num_rows($cekKolom) == 0) {

    mysqli_query($conn, "
        ALTER TABLE kegiatan 
        ADD dokumentasi VARCHAR(255) NULL AFTER waktu
    ");
}

/* =========================
   AMBIL DATA MASJID
========================= */

$masjid_q = mysqli_query($conn, "
    SELECT * FROM masjid 
    WHERE id='$masjid_id'
");

$masjid = mysqli_fetch_assoc($masjid_q);

$nama_masjid = $masjid 
    ? $masjid['nama_masjid'] 
    : 'Masjid';

/* =========================
   TAMBAH KEGIATAN
========================= */

if (isset($_POST['tambah_kegiatan'])) {

    $nama_kegiatan = mysqli_real_escape_string($conn, $_POST['nama_kegiatan']);
    $tanggal = $_POST['tanggal'];
    $waktu = $_POST['waktu'];

    $dokumentasi = "";

    if (!empty($_FILES['dokumentasi']['name'])) {

        $folder = "../assets/uploads/";

        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        $nama_file = time() . "_" . basename($_FILES['dokumentasi']['name']);
        $target = $folder . $nama_file;

        $ekstensi = strtolower(pathinfo($target, PATHINFO_EXTENSION));

        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ekstensi, $allowed)) {

            if (move_uploaded_file($_FILES['dokumentasi']['tmp_name'], $target)) {
                $dokumentasi = $nama_file;
            }
        }
    }

    mysqli_query($conn, "
        INSERT INTO kegiatan 
        (masjid_id, nama_kegiatan, tanggal, waktu, dokumentasi)
        VALUES 
        ('$masjid_id','$nama_kegiatan','$tanggal','$waktu','$dokumentasi')
    ");

    $_SESSION['success'] = "Kegiatan masjid berhasil ditambahkan";

    header("Location: kegiatan_masjid.php");
    exit;
}

/* =========================
   HAPUS KEGIATAN
========================= */

if (isset($_GET['hapus'])) {

    $id = $_GET['hapus'];

    mysqli_query($conn, "
        DELETE FROM kegiatan 
        WHERE id='$id' 
        AND masjid_id='$masjid_id'
    ");

    $_SESSION['success'] = "Kegiatan masjid berhasil dihapus";

    header("Location: kegiatan_masjid.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Kegiatan Masjid - SIMAKMU</title>
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
    background:linear-gradient(135deg,#00897b,#4db6ac);
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

.badge-new{
    background:rgba(76,161,175,.15);
    color:#277181;
}

.badge-done{
    background:rgba(108,117,125,.14);
    color:#5f6b73;
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

.preview-img{
    width:60px;
    height:60px;
    border-radius:14px;
    object-fit:cover;
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

.small-note{
    font-size:12px;
    color:#6f8190;
}

.search-box{
    max-width:320px;
}

.pagination .page-link{
    border:none;
    color:#20384c;
    border-radius:10px;
    margin:0 3px;
    font-weight:600;
}

.pagination .active .page-link{
    background:#4ca1af;
    color:white;
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
            Input Kegiatan Masjid

        </div>

        <form method="POST" enctype="multipart/form-data">

            <div class="mb-3">

                <label class="form-label">Nama Kegiatan</label>

                <input 
                    type="text"
                    name="nama_kegiatan"
                    class="form-control"
                    placeholder="Nama Kegiatan"
                    required
                >

            </div>

            <div class="mb-3">

                <label class="form-label">Tanggal</label>

                <input 
                    type="date"
                    name="tanggal"
                    class="form-control"
                    required
                >

            </div>

            <div class="mb-3">

                <label class="form-label">Waktu</label>

                <input 
                    type="time"
                    name="waktu"
                    class="form-control"
                    required
                >

            </div>

            <div class="mb-4">

                <label class="form-label">Dokumentasi</label>

                <input 
                    type="file"
                    name="dokumentasi"
                    class="form-control"
                    accept="image/*"
                >

                <div class="small-note mt-1">
                    Format: JPG, PNG, WEBP
                </div>

            </div>

            <button type="submit" name="tambah_kegiatan" class="btn btn-main w-100">

                <i class="bi bi-save me-1"></i>
                Simpan Kegiatan

            </button>

        </form>

    </div>

</div>

<!-- TABEL -->

<div class="col-lg-8">

    <div class="card dashboard-card p-4">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

            <div class="section-title mb-0">

                <i class="bi bi-calendar2-week"></i>
                Kegiatan <?= htmlspecialchars($nama_masjid); ?>

            </div>

            <!-- SEARCH -->

            <form method="GET" class="search-box w-100">

                <div class="input-group">

                    <input 
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Cari kegiatan..."
                        value="<?= htmlspecialchars($search); ?>"
                    >

                    <button class="btn btn-primary">

                        <i class="bi bi-search"></i>

                    </button>

                </div>

            </form>

        </div>

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle">

                <thead>

                    <tr>
                        <th width="7%">No</th>
                        <th>Nama Kegiatan</th>
                        <th width="15%">Tanggal</th>
                        <th width="12%">Waktu</th>
                        <th width="16%">Dokumentasi</th>
                        <th width="14%">Status</th>
                        <th width="10%">Aksi</th>
                    </tr>

                </thead>

                <tbody>

<?php

$kegiatan = mysqli_query($conn, "
    SELECT * FROM kegiatan
    WHERE masjid_id='$masjid_id'
    $whereSearch
    ORDER BY tanggal DESC, waktu DESC
    LIMIT $start, $limit
");

$totalData = mysqli_query($conn, "
    SELECT * FROM kegiatan
    WHERE masjid_id='$masjid_id'
    $whereSearch
");

$total = mysqli_num_rows($totalData);

$totalPages = ceil($total / $limit);

if(mysqli_num_rows($kegiatan) > 0){

    $no = $start + 1;

    while($k = mysqli_fetch_assoc($kegiatan)){

?>

<tr>

<td class="text-center"><?= $no++; ?></td>

<td>
    <strong>
        <?= htmlspecialchars($k['nama_kegiatan']); ?>
    </strong>
</td>

<td class="text-center">
    <?= date('d-m-Y', strtotime($k['tanggal'])); ?>
</td>

<td class="text-center">
    <?= date('H.i', strtotime($k['waktu'])); ?>
</td>

<td class="text-center">

<?php if(!empty($k['dokumentasi'])){ ?>

    <a href="../assets/uploads/<?= htmlspecialchars($k['dokumentasi']); ?>" target="_blank">

        <img 
            src="../assets/uploads/<?= htmlspecialchars($k['dokumentasi']); ?>" 
            class="preview-img"
        >

    </a>

<?php } else { ?>

    <span class="text-muted">-</span>

<?php } ?>

</td>

<td class="text-center">

<?php if($k['tanggal'] < date('Y-m-d')){ ?>

    <span class="badge-status badge-done">
        Terlaksana
    </span>

<?php } else { ?>

    <span class="badge-status badge-new">
        Terjadwal
    </span>

<?php } ?>

</td>

<td class="text-center">

    <a 
        href="?hapus=<?= $k['id']; ?>"
        class="btn btn-danger btn-delete"
        onclick="return confirm('Hapus kegiatan ini?')"
    >
        <i class="bi bi-trash"></i>
    </a>

</td>

</tr>

<?php
    }

}else{
?>

<tr>

<td colspan="7">

<div class="empty-data">

    <i class="bi bi-calendar-x"></i>

    <div>
        Belum ada data kegiatan masjid.
    </div>

</div>

</td>

</tr>

<?php } ?>

                </tbody>

            </table>

        </div>

        <!-- PAGINATION -->

<?php if($totalPages > 1){ ?>

<nav class="mt-4">

<ul class="pagination justify-content-center flex-wrap">

<?php for($i=1; $i <= $totalPages; $i++){ ?>

<li class="page-item <?= ($page == $i) ? 'active' : ''; ?>">

<a 
    class="page-link"
    href="?page=<?= $i; ?>&search=<?= urlencode($search); ?>"
>
    <?= $i; ?>
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