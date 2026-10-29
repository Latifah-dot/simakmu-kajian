<?php
session_start();
include "../config/koneksi.php";
/** @var mysqli $conn */
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

/* =========================
   PAGINATION + SEARCH
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

$where = "WHERE role='mubaligh'";

if($search != ''){
    $where .= " AND nama LIKE '%$search%'";
}

/* =========================
   TOTAL DATA
========================= */

$totalQuery = mysqli_query($conn,"
    SELECT *
    FROM users
    $where
");

$totalData = mysqli_num_rows($totalQuery);

$totalPage = ceil($totalData / $limit);

/* =========================
   TAMBAH MUBALIGH
========================= */

if(isset($_POST['simpan'])){

    $nama     = mysqli_real_escape_string($conn,$_POST['nama']);
    $email    = mysqli_real_escape_string($conn,$_POST['email']);
    $password = mysqli_real_escape_string($conn,$_POST['password']);

    mysqli_query($conn,"
        INSERT INTO users (nama,email,password,role)
        VALUES ('$nama','$email','$password','mubaligh')
    ");

    $_SESSION['success'] = "Data mubaligh berhasil ditambahkan";

    header("Location: daftar_mubaligh.php");
    exit;
}

/* =========================
   HAPUS
========================= */

if(isset($_GET['hapus'])){

    $id = $_GET['hapus'];

    mysqli_query($conn,"
        DELETE FROM users
        WHERE id='$id'
    ");

    $_SESSION['success'] = "Data mubaligh berhasil dihapus";

    header("Location: daftar_mubaligh.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Daftar Mubaligh - SIMAKMU</title>
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

.badge-role{
    background:rgba(66,165,245,.15);
    color:#1565c0;
    border-radius:50px;
    padding:8px 12px;
    font-size:12px;
    font-weight:700;
}

.btn-delete{
    border-radius:10px;
}

.search-box{
    max-width:260px;
}

.pagination .page-link{
    border:none;
    color:#1565c0;
    border-radius:10px;
    margin:0 3px;
    font-weight:600;
}

.pagination .active .page-link{
    background:#1565c0;
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

    <a href="admin.php" class="btn btn-light back-btn">

        <i class="bi bi-arrow-left me-1"></i>
        Kembali

    </a>

</div>

<div class="container-fluid position-relative" style="z-index:2;">

<div class="row g-4">

<!-- INPUT -->

<div class="col-lg-4">

    <div class="card dashboard-card p-4">

        <div class="section-title">

            <i class="bi bi-person-plus-fill"></i>
            Input Mubaligh

        </div>

        <form method="POST">

            <div class="mb-3">

                <input
                    type="text"
                    name="nama"
                    class="form-control"
                    placeholder="Nama Mubaligh"
                    required
                >

            </div>

            <div class="mb-3">

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    placeholder="Email"
                    required
                >

            </div>

            <div class="mb-3">

                <input
                    type="text"
                    name="password"
                    class="form-control"
                    placeholder="NBM"
                    required
                >

            </div>

            <button type="submit" name="simpan" class="btn btn-main w-100">

                <i class="bi bi-save me-1"></i>
                Simpan Mubaligh

            </button>

        </form>

    </div>

</div>

<!-- TABEL -->

<div class="col-lg-8">

    <div class="card dashboard-card p-4">

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

            <div class="section-title mb-0">

                <i class="bi bi-people-fill"></i>
                Daftar Mubaligh

            </div>

            <span class="badge-role">

                Total Mubaligh :

                <?php
                $count = mysqli_query($conn,"
                    SELECT *
                    FROM users
                    WHERE role='mubaligh'
                ");

                echo mysqli_num_rows($count);
                ?>

            </span>

        </div>

        <!-- SEARCH -->

        <form method="GET" class="mb-4">

            <div class="input-group search-box">

                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Cari mubaligh..."
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
                        <th width="8%">NO</th>
                        <th>NAMA</th>
                        <th>EMAIL</th>
                        <th width="18%">ROLE</th>
                        <th width="15%">AKSI</th>
                    </tr>

                </thead>

                <tbody>

                    <?php

                    $no = $start + 1;

                    $mubaligh = mysqli_query($conn,"
                        SELECT *
                        FROM users
                        $where
                        ORDER BY id DESC
                        LIMIT $start,$limit
                    ");

                    if(mysqli_num_rows($mubaligh)>0){

                        while($m = mysqli_fetch_assoc($mubaligh)){

                    ?>

                    <tr>

                        <td><?= $no++; ?></td>

                        <td>
                            <strong>
                                <?= htmlspecialchars($m['nama']); ?>
                            </strong>
                        </td>

                        <td>
                            <?= htmlspecialchars($m['email']); ?>
                        </td>

                        <td>

                            <span class="badge-role">
                                Mubaligh
                            </span>

                        </td>

                        <td>

                            <a
                                href="?hapus=<?= $m['id']; ?>"
                                class="btn btn-danger btn-sm btn-delete"
                                title="Hapus Mubaligh"
                                onclick="return confirm('Yakin hapus mubaligh ini?')"
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

                        <td colspan="5" class="text-center text-muted">
                            Data mubaligh tidak ditemukan.
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

                <?php for($i=1; $i <= $totalPage; $i++){ ?>

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