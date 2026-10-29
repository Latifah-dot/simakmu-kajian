<?php
session_start();
include "../config/koneksi.php";
/** @var mysqli $conn */

date_default_timezone_set('Asia/Jakarta');

if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit;
}

$user = $_SESSION['user'];
$mubaligh_id = $user['id'];

$today = date('Y-m-d');
$now = date('H:i:s');

/*
|--------------------------------------------------------------------------
| AMBIL JADWAL
|--------------------------------------------------------------------------
*/

if(isset($_GET['ambil'])){

    $id = $_GET['ambil'];

    $cek = mysqli_query($conn,"
        SELECT *
        FROM kajian_ahad
        WHERE id='$id'
        AND (
            status='pending'
            OR status='menunggu'
        )
    ");

    if(mysqli_num_rows($cek) > 0){

        mysqli_query($conn,"
            UPDATE kajian_ahad
            SET
                mubaligh_id='$mubaligh_id',
                status='disetujui'
            WHERE id='$id'
        ");

        $_SESSION['success'] = "Jadwal berhasil diambil.";
    }

    header("Location: konfirmasi_jadwal.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Konfirmasi Jadwal - SIMAKMU</title>
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
    z-index:2;
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:28px;
    color:white;
}

.brand{
    display:flex;
    align-items:center;
    gap:14px;
}

.brand img{
    width:56px;
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

.back-btn{
    background:white;
    color:#20384c;
    text-decoration:none;
    border-radius:14px;
    padding:11px 18px;
    font-weight:700;
}

.back-btn:hover{
    color:#20384c;
}

.card-box{
    position:relative;
    z-index:2;
    background:rgba(255,255,255,.96);
    border-radius:28px;
    padding:30px;
    box-shadow:0 20px 45px rgba(0,0,0,.15);
}

.title{
    font-size:24px;
    font-weight:800;
    color:#20384c;
    margin-bottom:22px;
}

.search-box{
    margin-bottom:20px;
}

.search-input{
    height:52px;
    border-radius:15px;
    border:1px solid #dbe7ec;
    padding:0 16px;
    width:100%;
    font-size:14px;
}

.search-input:focus{
    outline:none;
    border-color:#4ca1af;
}

.table{
    margin-bottom:0;
}

.table thead th{
    background:#eef6f8;
    color:#20384c;
    font-size:13px;
    font-weight:800;
    text-transform:uppercase;
    text-align:center;
    vertical-align:middle;
}

.table td{
    vertical-align:middle;
    padding:14px 12px;
    font-size:14px;
}

.badge-status{
    border-radius:50px;
    padding:7px 14px;
    font-size:12px;
    font-weight:800;
}

.badge-pending{
    background:rgba(255,193,7,.16);
    color:#8a6d00;
}

.btn-ambil{
    border:none;
    border-radius:12px;
    background:#198754;
    color:white;
    padding:9px 14px;
    font-size:13px;
    font-weight:700;
    text-decoration:none;
}

.btn-ambil:hover{
    background:#157347;
    color:white;
}

.empty{
    text-align:center;
    padding:45px 10px;
    color:#7d8c99;
}

.empty i{
    font-size:54px;
    margin-bottom:10px;
    color:#b7c6cf;
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

    .wrapper{
        padding:20px;
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

<div class="wrapper">

<div class="circle circle1"></div>
<div class="circle circle2"></div>

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

    <a href="mubaligh.php" class="back-btn">

        <i class="bi bi-arrow-left me-1"></i>
        Kembali

    </a>

</div>

<div class="card-box">

    <div class="title">

        Konfirmasi Jadwal Kajian

    </div>

    <div class="search-box">

        <input
            type="text"
            id="searchInput"
            class="search-input"
            placeholder="Cari masjid / judul kajian..."
        >

    </div>

    <div class="table-responsive">

        <table class="table table-bordered table-hover align-middle">

            <thead>

                <tr>
                    <th width="7%">No</th>
                    <th>Masjid</th>
                    <th>Judul Kajian</th>
                    <th width="16%">Tanggal</th>
                    <th width="12%">Waktu</th>
                    <th width="14%">Status</th>
                    <th width="14%">Aksi</th>
                </tr>

            </thead>

            <tbody id="tableData">

                <?php

                $no = 1;

                $jadwal = mysqli_query($conn,"
    SELECT *
    FROM kajian_ahad
    WHERE status='menunggu'
    ORDER BY tanggal ASC, waktu ASC
");

                if(mysqli_num_rows($jadwal) > 0){

                    while($j = mysqli_fetch_assoc($jadwal)){

                ?>

                <tr>

                    <td class="text-center">
                        <?= $no++; ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($j['tempat']); ?>
                    </td>

                    <td>
                        <strong>
                            <?= htmlspecialchars($j['judul']); ?>
                        </strong>
                    </td>

                    <td class="text-center">
                        <?= date('d-m-Y', strtotime($j['tanggal'])); ?>
                    </td>

                    <td class="text-center">
                        <?= date('H:i', strtotime($j['waktu'])); ?>
                    </td>

                    <td class="text-center">

                        <span class="badge-status badge-pending">
                            Menunggu
                        </span>

                    </td>

                    <td class="text-center">

                        <a
                            href="?ambil=<?= $j['id']; ?>"
                            class="btn-ambil"
                            onclick="return confirm('Ambil jadwal ini?')"
                        >
                            <i class="bi bi-check-circle me-1"></i>
                            Ambil
                        </a>

                    </td>

                </tr>

                <?php
                    }

                } else {
                ?>

                <tr>

                    <td colspan="7">

                        <div class="empty">

                            <i class="bi bi-calendar-x"></i>

                            <div>
                                Tidak ada jadwal yang perlu dikonfirmasi.
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

<div class="modal fade" id="successModal">

<div class="modal-dialog modal-dialog-centered">

<div class="modal-content">

    <div class="success-icon">
        <i class="bi bi-check-lg"></i>
    </div>

    <h5 class="fw-bold">Berhasil</h5>

    <p class="mb-0">

        <?= isset($_SESSION['success']) ? $_SESSION['success'] : ''; ?>

    </p>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>

const searchInput = document.getElementById('searchInput');

searchInput.addEventListener('keyup', function(){

    let keyword = this.value.toLowerCase();

    let rows = document.querySelectorAll('#tableData tr');

    rows.forEach(function(row){

        let text = row.innerText.toLowerCase();

        if(text.includes(keyword)){
            row.style.display = '';
        }else{
            row.style.display = 'none';
        }

    });

});

</script>

<?php if(isset($_SESSION['success'])){ ?>

<script>

var modal = new bootstrap.Modal(
    document.getElementById('successModal')
);

modal.show();

setTimeout(() => {
    modal.hide();
},3000);

</script>

<?php unset($_SESSION['success']); } ?>

</body>
</html>