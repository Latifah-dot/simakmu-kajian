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

/* =========================
   SIMPAN MASJID
========================= */
if (isset($_POST['simpan_masjid'])) {

    $nama_masjid = mysqli_real_escape_string($conn, $_POST['nama_masjid']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);

    mysqli_query($conn,"
        INSERT INTO masjid (nama_masjid, alamat)
        VALUES ('$nama_masjid','$alamat')
    ");

    $_SESSION['success'] = "Data masjid berhasil ditambahkan";
    header("Location: daftar_masjid.php");
    exit;
}

/* =========================
   HAPUS MASJID
========================= */
if (isset($_GET['hapus'])) {

    $id = $_GET['hapus'];

    mysqli_query($conn,"
        DELETE FROM masjid
        WHERE id='$id'
    ");

    $_SESSION['success'] = "Data masjid berhasil dihapus";
    header("Location: daftar_masjid.php");
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

$search = "";

$where = "";

if(isset($_GET['search']) && $_GET['search'] != ""){

    $search = mysqli_real_escape_string($conn,$_GET['search']);

    $where = "WHERE nama_masjid LIKE '%$search%'
              OR alamat LIKE '%$search%'";
}

/* TOTAL DATA */

$totalQuery = mysqli_query($conn,"
    SELECT COUNT(*) as total
    FROM masjid
    $where
");

$totalData = mysqli_fetch_assoc($totalQuery)['total'];

$totalPage = ceil($totalData / $limit);

/* DATA MASJID */

$masjid = mysqli_query($conn,"
    SELECT *
    FROM masjid
    $where
    ORDER BY id DESC
    LIMIT $start,$limit
");

?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Daftar Masjid - SIMAKMU</title>
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

.form-control{
    min-height:48px;
    border-radius:14px;
    border:1px solid #dbe7ec;
    font-size:14px;
}

.form-control:focus{
    border-color:#4ca1af;
    box-shadow:0 0 0 .20rem rgba(76,161,175,.18);
}

.btn-main{
    min-height:48px;
    border:none;
    border-radius:14px;
    background:linear-gradient(135deg,#2c3e50,#4ca1af);
    color:white;
    font-weight:700;
}

.btn-main:hover{
    color:white;
    transform:translateY(-2px);
    box-shadow:0 12px 24px rgba(44,62,80,.20);
}

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

.badge-masjid{
    background:rgba(76,161,175,.14);
    color:#287485;
    border-radius:50px;
    padding:8px 12px;
    font-size:12px;
    font-weight:700;
}

.btn-delete{
    border-radius:10px;
}

.back-btn{
    border-radius:12px;
    padding:10px 16px;
    font-weight:700;
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

.pagination .page-link{
    border:none;
    color:#2c3e50;
    margin:0 3px;
    border-radius:10px;
    font-weight:600;
}

.pagination .active .page-link{
    background:#2c3e50;
    color:white;
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

    <a href="admin.php" class="btn btn-light back-btn">
        <i class="bi bi-arrow-left me-1"></i>
        Kembali Dashboard
    </a>

</div>

<div class="container-fluid position-relative" style="z-index:2;">

<div class="row g-4">

<!-- FORM INPUT -->

<div class="col-lg-4">

    <div class="card dashboard-card p-4">

        <div class="section-title">
            <i class="bi bi-building-add"></i>
            Input Masjid
        </div>

        <form method="POST">

            <div class="mb-3">

                <input 
                    type="text"
                    name="nama_masjid"
                    class="form-control"
                    placeholder="Nama Masjid Muhammadiyah"
                    required
                >

            </div>

            <div class="mb-3">

                <input 
                    type="text"
                    name="alamat"
                    class="form-control"
                    placeholder="Alamat / Kecamatan"
                    required
                >

            </div>

            <button type="submit" name="simpan_masjid" class="btn btn-main w-100">

                <i class="bi bi-save me-1"></i>
                Simpan Masjid

            </button>

        </form>

    </div>

</div>

<!-- TABEL MASJID -->

<div class="col-lg-8">

    <div class="card dashboard-card p-4">

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

            <div class="section-title mb-0">
                <i class="bi bi-building"></i>
                Daftar Masjid Muhammadiyah
            </div>

            <span class="badge-masjid">
                Total Masjid :
                <?php
                $countMasjid = mysqli_query($conn,"SELECT * FROM masjid");
                echo mysqli_num_rows($countMasjid);
                ?>
            </span>

        </div>

        <!-- SEARCH -->

        <form method="GET" class="mb-4">

            <div class="input-group">

                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Cari nama masjid atau alamat..."
                    value="<?= htmlspecialchars($search); ?>"
                >

                <button class="btn btn-dark">

                    <i class="bi bi-search"></i>

                </button>

            </div>

        </form>

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle">

                <thead>

                    <tr>
                        <th width="8%">NO</th>
                        <th>NAMA MASJID</th>
                        <th>ALAMAT</th>
                        <th width="15%">AKSI</th>
                    </tr>

                </thead>

                <tbody>

                    <?php

                    $no = $start + 1;

                    if(mysqli_num_rows($masjid)>0){

                        while($m = mysqli_fetch_assoc($masjid)){

                    ?>

                    <tr>

                        <td><?= $no++; ?></td>

                        <td>
                            <strong>
                                <?= htmlspecialchars($m['nama_masjid']); ?>
                            </strong>
                        </td>

                        <td>
                            <?= htmlspecialchars($m['alamat']); ?>
                        </td>

                        <td>

                            <div class="d-flex gap-2">

                                <a 
                                    href="profil_masjid.php?id=<?= $m['id']; ?>"
                                    class="btn btn-primary btn-sm btn-delete"
                                    title="Lihat Profil Masjid"
                                >
                                    <i class="bi bi-eye-fill"></i>
                                </a>

                                <a 
                                    href="?hapus=<?= $m['id']; ?>"
                                    class="btn btn-danger btn-sm btn-delete"
                                    title="Hapus Masjid"
                                >
                                    <i class="bi bi-trash"></i>
                                </a>

                            </div>

                        </td>

                    </tr>

                    <?php

                        }

                    } else {

                    ?>

                    <tr>

                        <td colspan="4" class="text-center text-muted">
                            Belum ada data masjid.
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

                <a class="page-link"
                   href="?page=<?= $page-1; ?>&search=<?= $search; ?>">

                    Previous

                </a>

            </li>

            <?php } ?>

            <?php for($i=1; $i<=$totalPage; $i++){ ?>

            <li class="page-item <?= ($i==$page)?'active':''; ?>">

                <a class="page-link"
                   href="?page=<?= $i; ?>&search=<?= $search; ?>">

                    <?= $i; ?>

                </a>

            </li>

            <?php } ?>

            <?php if($page < $totalPage){ ?>

            <li class="page-item">

                <a class="page-link"
                   href="?page=<?= $page+1; ?>&search=<?= $search; ?>">

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