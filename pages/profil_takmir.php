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

/* AMBIL DATA MASJID */
$masjid_q = mysqli_query($conn, "SELECT * FROM masjid WHERE id='$masjid_id'");
$masjid = mysqli_fetch_assoc($masjid_q);

if (!$masjid) {
    header("Location: takmir.php");
    exit;
}

/* UPDATE PROFIL MASJID */
if (isset($_POST['update_profil'])) {

    $nama_masjid = mysqli_real_escape_string($conn, $_POST['nama_masjid']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);

    $foto_lama = $masjid['foto'];
    $foto_baru = $foto_lama;

    if (!empty($_FILES['foto']['name'])) {

        $folder = "../assets/uploads/";

        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        $nama_file = time() . "_" . basename($_FILES['foto']['name']);
        $target = $folder . $nama_file;

        $ekstensi = strtolower(pathinfo($target, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ekstensi, $allowed)) {
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target)) {
                $foto_baru = $nama_file;
            }
        }
    }

    mysqli_query($conn, "
        UPDATE masjid 
        SET nama_masjid='$nama_masjid',
            alamat='$alamat',
            foto='$foto_baru'
        WHERE id='$masjid_id'
    ");

    $_SESSION['success'] = "Profil masjid berhasil diperbarui";
    header("Location: profil_takmir.php");
    exit;
}
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
    margin-bottom:22px;
}

.section-title i{
    color:#4ca1af;
}

.profile-banner{
    background:linear-gradient(135deg,#2c3e50,#4ca1af);
    border-radius:26px;
    padding:34px;
    color:white;
    overflow:hidden;
    position:relative;
    height:100%;
}

.profile-banner::before{
    content:'';
    position:absolute;
    width:250px;
    height:250px;
    border-radius:50%;
    background:rgba(255,255,255,.08);
    top:-90px;
    right:-70px;
}

.masjid-photo{
    width:145px;
    height:145px;
    border-radius:28px;
    background:rgba(255,255,255,.15);
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
    margin-bottom:22px;
    position:relative;
    z-index:2;
}

.masjid-photo img{
    width:100%;
    height:100%;
    object-fit:cover;
}

.masjid-photo i{
    font-size:70px;
}

.masjid-name{
    font-size:30px;
    font-weight:900;
    margin-bottom:10px;
    position:relative;
    z-index:2;
}

.masjid-address{
    font-size:15px;
    opacity:.95;
    line-height:1.6;
    position:relative;
    z-index:2;
}

.form-control{
    min-height:52px;
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
    margin-bottom:8px;
}

.btn-main{
    min-height:52px;
    border:none;
    border-radius:15px;
    background:linear-gradient(135deg,#2c3e50,#4ca1af);
    color:white;
    font-weight:700;
}

.btn-main:hover{
    color:white;
    transform:translateY(-2px);
}

.small-note{
    font-size:12px;
    color:#6f8190;
    margin-top:6px;
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
            <div class="brand-subtitle">Sistem Informasi Manajemen Kajian Masjid Muhammadiyah</div>
        </div>
    </div>

    <a href="takmir.php" class="btn btn-light back-btn">
        <i class="bi bi-arrow-left me-1"></i>
        Kembali
    </a>

</div>

<div class="container-fluid position-relative" style="z-index:2;">

<div class="row g-4">

<!-- PREVIEW PROFIL -->
<div class="col-lg-5">

    <div class="card dashboard-card p-4 h-100">

        <div class="profile-banner">

            <div class="masjid-photo">
                <?php if (!empty($masjid['foto'])) { ?>
                    <img src="../assets/uploads/<?= htmlspecialchars($masjid['foto']); ?>" alt="Foto Masjid">
                <?php } else { ?>
                    <i class="bi bi-building-fill"></i>
                <?php } ?>
            </div>

            <div class="masjid-name">
                <?= htmlspecialchars($masjid['nama_masjid']); ?>
            </div>

            <div class="masjid-address">
                <i class="bi bi-geo-alt-fill me-1"></i>
                <?= htmlspecialchars($masjid['alamat']); ?>
            </div>

        </div>

    </div>

</div>

<!-- FORM UPDATE -->
<div class="col-lg-7">

    <div class="card dashboard-card p-4">

        <div class="section-title">
            <i class="bi bi-building-gear"></i>
            Kelola Profil Masjid
        </div>

        <form method="POST" enctype="multipart/form-data">

            <div class="mb-3">
                <label class="form-label">Nama Masjid</label>
                <input 
                    type="text" 
                    name="nama_masjid" 
                    class="form-control" 
                    value="<?= htmlspecialchars($masjid['nama_masjid']); ?>" 
                    required
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Alamat / Kecamatan</label>
                <input 
                    type="text" 
                    name="alamat" 
                    class="form-control" 
                    value="<?= htmlspecialchars($masjid['alamat']); ?>" 
                    required
                >
            </div>

            <div class="mb-4">
                <label class="form-label">Foto Masjid</label>
                <input 
                    type="file" 
                    name="foto" 
                    class="form-control" 
                    accept="image/*"
                >
                <div class="small-note">
                    Kosongkan jika tidak ingin mengganti foto. Format yang disarankan: JPG, PNG, atau WEBP.
                </div>
            </div>

            <button type="submit" name="update_profil" class="btn btn-main w-100">
                <i class="bi bi-save me-1"></i>
                Simpan Profil Masjid
            </button>

        </form>

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