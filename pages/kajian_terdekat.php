<?php
session_start();
include "../config/koneksi.php";
/** @var mysqli $conn */
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$today = date('Y-m-d');

/* =========================
   CEK KOLOM MUBALIGH
========================= */
$cekKolom = mysqli_query($conn, "SHOW COLUMNS FROM kajian_ahad LIKE 'mubaligh_id'");
if (mysqli_num_rows($cekKolom) == 0) {
    mysqli_query($conn, "ALTER TABLE kajian_ahad ADD mubaligh_id INT(11) NULL AFTER tempat");
}

/* =========================
   FUNCTION
========================= */
function clean($conn, $data){
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}

function formatTanggalIndo($tanggal){
    return date('d-m-Y', strtotime($tanggal));
}

function formatJam($waktu){
    return date('H.i', strtotime($waktu));
}

function formatNotifUpdate($judul, $tanggal, $waktu, $tempat, $mubaligh){
    $today = date('Y-m-d');
    $besok = date('Y-m-d', strtotime('+1 day'));

    if ($tanggal == $today) {
        $hari = "hari ini";
    } elseif ($tanggal == $besok) {
        $hari = "besok";
    } else {
        $hari = date('d-m-Y', strtotime($tanggal));
    }

    $jam = date('H.i', strtotime($waktu));

    return "Update Kajian: Kajian dengan tema \"$judul\" akan dilaksanakan $hari pukul $jam di $tempat dengan mubaligh $mubaligh.";
}

/* =========================
   AMBIL DATA YANG DIEDIT
========================= */
$idKajian = isset($_GET['id']) ? $_GET['id'] : null;

if ($idKajian) {
    $queryKajian = mysqli_query($conn,"
        SELECT k.*, u.nama AS nama_mubaligh
        FROM kajian_ahad k
        LEFT JOIN users u ON k.mubaligh_id = u.id
        WHERE k.id='$idKajian'
        LIMIT 1
    ");
} else {
    $queryKajian = mysqli_query($conn,"
        SELECT k.*, u.nama AS nama_mubaligh
        FROM kajian_ahad k
        LEFT JOIN users u ON k.mubaligh_id = u.id
        WHERE k.tanggal >= '$today'
        ORDER BY k.tanggal ASC, k.waktu ASC
        LIMIT 1
    ");
}

$kajian = mysqli_fetch_assoc($queryKajian);

/* =========================
   UPDATE KAJIAN
========================= */
if(isset($_POST['update_kajian'])){

    $id          = clean($conn, $_POST['id']);
    $judul       = clean($conn, $_POST['judul']);
    $tanggal     = clean($conn, $_POST['tanggal']);
    $waktu       = clean($conn, $_POST['waktu']);
    $tempat      = clean($conn, $_POST['tempat']);
    $mubaligh_id = clean($conn, $_POST['mubaligh_id']);

    if($tanggal < $today){
        $_SESSION['error'] = "Tanggal tidak boleh kurang dari hari ini.";
        header("Location: kajian_terdekat.php?id=$id");
        exit;
    }

    $cekBentrok = mysqli_query($conn,"
        SELECT k.*, u.nama AS nama_mubaligh
        FROM kajian_ahad k
        LEFT JOIN users u ON k.mubaligh_id = u.id
        WHERE k.id != '$id'
        AND k.tanggal = '$tanggal'
        AND k.waktu = '$waktu'
        AND (k.tempat = '$tempat' OR k.mubaligh_id = '$mubaligh_id')
        LIMIT 1
    ");

    if(mysqli_num_rows($cekBentrok) > 0){

        $bentrok = mysqli_fetch_assoc($cekBentrok);

        $_SESSION['error'] = "Jadwal sudah terisi atau bentrok dengan kajian: " .
        $bentrok['judul'] . " pada tanggal " . date('d-m-Y', strtotime($bentrok['tanggal'])) .
        " pukul " . date('H.i', strtotime($bentrok['waktu'])) .
        " di " . $bentrok['tempat'] .
        " oleh " . ($bentrok['nama_mubaligh'] ? $bentrok['nama_mubaligh'] : '-');

        header("Location: kajian_terdekat.php?id=$id");
        exit;
    }

    mysqli_query($conn,"
        UPDATE kajian_ahad
        SET judul='$judul',
            tanggal='$tanggal',
            waktu='$waktu',
            tempat='$tempat',
            mubaligh_id='$mubaligh_id'
        WHERE id='$id'
    ");

    $_SESSION['success'] = "Jadwal kajian berhasil diperbarui";
    header("Location: kajian_terdekat.php?id=$id");
    exit;
}

/* =========================
   KIRIM NOTIFIKASI
========================= */
if(isset($_POST['kirim_notif'])){

    $judul       = clean($conn, $_POST['judul']);
    $tanggal     = clean($conn, $_POST['tanggal']);
    $waktu       = clean($conn, $_POST['waktu']);
    $tempat      = clean($conn, $_POST['tempat']);
    $mubaligh_id = clean($conn, $_POST['mubaligh_id']);
    $id          = clean($conn, $_POST['id']);

    // ambil nama mubaligh
    $getMubaligh = mysqli_query($conn,"
        SELECT nama 
        FROM users 
        WHERE id='$mubaligh_id'
    ");

    $mubaligh = mysqli_fetch_assoc($getMubaligh);
    $namaMubaligh = $mubaligh ? $mubaligh['nama'] : "-";

    $judulNotif = "Informasi Kajian";
    $pesan = formatNotifUpdate($judul, $tanggal, $waktu, $tempat, $namaMubaligh);

    // 1. SIMPAN NOTIF UTAMA
    mysqli_query($conn,"
        INSERT INTO notifikasi (judul, pesan, tujuan, status)
        VALUES ('$judulNotif', '$pesan', 'semua', 'unread')
    ");

    // ambil id notif terakhir
    $notif_id = mysqli_insert_id($conn);

    /* =====================================
       2. KIRIM KE TAKMIR (SEMUA TAKMIR)
    ===================================== */
    $takmir = mysqli_query($conn,"
        SELECT id FROM users WHERE role='takmir'
    ");

    while($t = mysqli_fetch_assoc($takmir)){
        $uid = $t['id'];

        mysqli_query($conn,"
            INSERT INTO notifikasi_user (user_id, notif_id, status)
            VALUES ('$uid', '$notif_id', 'unread')
        ");
    }

    /* =====================================
       3. KIRIM KE MUBALIGH
    ===================================== */
    mysqli_query($conn,"
        INSERT INTO notifikasi_user (user_id, notif_id, status)
        VALUES ('$mubaligh_id', '$notif_id', 'unread')
    ");

    $_SESSION['success'] = "Notifikasi berhasil dikirim ke Takmir & Mubaligh";
    header("Location: kajian_terdekat.php?id=$id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Kajian Terdekat - SIMAKMU</title>
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

.btn-update{
    min-height:52px;
    border:none;
    border-radius:15px;
    background:linear-gradient(135deg,#198754,#4dbb7c);
    color:white;
    font-weight:700;
}

.btn-update:hover{
    color:white;
    transform:translateY(-2px);
}

.btn-notif{
    min-height:52px;
    border:none;
    border-radius:15px;
    background:linear-gradient(135deg,#f9a825,#ffca28);
    color:#20384c;
    font-weight:800;
}

.btn-notif:hover{
    color:#20384c;
    transform:translateY(-2px);
}

.form-card{
    max-width:650px;
    margin:auto;
}

.info-note{
    background:rgba(76,161,175,.13);
    color:#287485;
    border-radius:16px;
    padding:14px 16px;
    font-size:13px;
    line-height:1.6;
    margin-bottom:20px;
}

.empty-data{
    text-align:center;
    padding:55px 15px;
    color:#7c8d99;
}

.empty-data i{
    font-size:54px;
    color:#b8c7cf;
    margin-bottom:12px;
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

    <div class="card dashboard-card p-4 form-card">

        <div class="section-title">
            <i class="bi bi-bell-fill"></i>
            Kajian Terdekat
        </div>

        <?php if($kajian){ ?>

        <div class="info-note">
            <i class="bi bi-info-circle me-1"></i>
            Halaman ini digunakan untuk memperbarui jadwal kajian dan mengirimkan notifikasi kepada takmir serta mubaligh.
        </div>

        <form method="POST">

            <input type="hidden" name="id" value="<?= $kajian['id']; ?>">

            <div class="mb-3">
                <input
                    type="text"
                    name="judul"
                    class="form-control"
                    value="<?= htmlspecialchars($kajian['judul']); ?>"
                    required
                >
            </div>

            <div class="mb-3">
                <input
                    type="date"
                    name="tanggal"
                    class="form-control"
                    min="<?= date('Y-m-d'); ?>"
                    value="<?= $kajian['tanggal']; ?>"
                    required
                >
            </div>

            <div class="mb-3">
                <input
                    type="time"
                    name="waktu"
                    class="form-control"
                    value="<?= $kajian['waktu']; ?>"
                    required
                >
            </div>

            <div class="mb-3">
                <input
                    type="text"
                    name="tempat"
                    class="form-control"
                    value="<?= htmlspecialchars($kajian['tempat']); ?>"
                    placeholder="Tempat Kajian"
                    required
                >
            </div>

            <div class="mb-4">
                <select name="mubaligh_id" class="form-select" required>
                    <option value="">Pilih Mubaligh</option>

                    <?php
                    $mubaligh = mysqli_query($conn,"
                        SELECT *
                        FROM users
                        WHERE role='mubaligh'
                        ORDER BY nama ASC
                    ");

                    while($m = mysqli_fetch_assoc($mubaligh)){
                        $selected = ($kajian['mubaligh_id'] == $m['id']) ? 'selected' : '';
                    ?>
                        <option value="<?= $m['id']; ?>" <?= $selected; ?>>
                            <?= htmlspecialchars($m['nama']); ?>
                        </option>
                    <?php } ?>

                </select>
            </div>

            <button type="submit" name="update_kajian" class="btn btn-update w-100 mb-2">
                <i class="bi bi-pencil-square me-1"></i>
                Update Kajian
            </button>

            <button type="submit" name="kirim_notif" class="btn btn-notif w-100">
                <i class="bi bi-send-fill me-1"></i>
                Kirim Notifikasi
            </button>

        </form>

        <?php } else { ?>

            <div class="empty-data">
                <i class="bi bi-calendar-x"></i>
                <div>Belum ada kajian terdekat.</div>
            </div>

        <?php } ?>

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

<!-- ERROR MODAL -->
<div class="modal fade" id="errorModal">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">

    <div class="error-icon">
        <i class="bi bi-exclamation-triangle-fill"></i>
    </div>

    <h5 class="fw-bold">Peringatan</h5>

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
</script>
<?php unset($_SESSION['error']); } ?>

</body>
</html>