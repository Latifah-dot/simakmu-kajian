<?php
session_start();
include "../config/koneksi.php";
/** @var mysqli $conn */

$error = "";

if (isset($_POST['login'])) {

    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $data = mysqli_query($conn, "SELECT * FROM users 
    WHERE email='$email' 
    AND password='$password' 
    AND role='$role'");

    $user = mysqli_fetch_assoc($data);

    if ($user) {

        $_SESSION['user'] = $user;

        if ($role == 'admin') {

            header("Location: ../pages/admin.php");

        } elseif ($role == 'takmir') {

            header("Location: ../pages/takmir.php");

        } else {

            header("Location: ../pages/mubaligh.php");
        }

        exit;

    } else {

        $error = true;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>

    <meta charset="UTF-8">
    <title>Login SIMAKMU</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- FONT -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:'Poppins',sans-serif;
        }

        body{
            min-height:100vh;
            overflow:hidden;
            background:#081b3a;
        }

        .main-wrapper{
            width:100%;
            height:100vh;
            display:flex;
        }

        /* =======================
           LEFT SIDE
        ======================= */

        .left-side{

            width:68%;

            position:relative;

            overflow:hidden;

            background:
            linear-gradient(
            rgba(0,18,55,.86),
            rgba(0,45,110,.75)
            ),
            url('../assets/masjid-bg.jpg');

            background-size:cover;
            background-position:center bottom;

            display:flex;
            justify-content:center;
            align-items:center;

            padding:60px 70px;
        }

        /* EFEK CAHAYA */

        .left-side::before{

            content:'';

            position:absolute;
            inset:0;

            background:
            radial-gradient(
            circle at top left,
            rgba(255,255,255,.10),
            transparent 32%);
        }

        /* SILUET MASJID */

        .left-side::after{

            content:'';

            position:absolute;

            bottom:-20px;
            left:-40px;

            width:110%;
            height:320px;

            background:url('../assets/siluet-masjid.jpg');

            background-size:cover;
            background-repeat:no-repeat;
            background-position:bottom;

            opacity:.12;

            z-index:1;
        }

        .content{

            position:relative;
            z-index:3;

            width:100%;
            max-width:700px;

            text-align:center;

            display:flex;
            flex-direction:column;
            align-items:center;

            color:white;
        }

        .main-logo{

            width:100px;

            margin-bottom:26px;
        }

        .hero-title{

            font-size:58px;
            line-height:1.1;

            font-weight:800;

            margin-bottom:10px;

            white-space:nowrap;

            letter-spacing:1px;
        }

        .hero-subtitle{

            font-size:16px;

            color:#d6e7ff;

            margin-bottom:18px;
        }

        .simakmu-title{

            font-size:34px;

            font-weight:800;

            letter-spacing:3px;

            margin-bottom:16px;
        }

        .line{

            width:90px;
            height:5px;

            border-radius:30px;

            background:#71c7ff;

            margin-bottom:28px;
        }

        .hero-desc{

            font-size:15px;
            line-height:1.9;

            color:#edf4ff;

            max-width:650px;
        }

        .ayat-box{

            margin-top:42px;

            background:rgba(255,255,255,.10);

            backdrop-filter:blur(12px);

            border:1px solid rgba(255,255,255,.10);

            border-radius:24px;

            padding:24px 28px;

            width:100%;
            max-width:650px;
        }

        .ayat-box p{

            font-size:14px;
            line-height:1.9;

            margin-bottom:10px;
        }

        .ayat-box span{

            color:#8fd1ff;
            font-weight:600;
        }

        /* =======================
           RIGHT SIDE
        ======================= */

        .right-side{

            width:32%;

            background:#f7fbff;

            display:flex;
            justify-content:center;
            align-items:center;

            padding:35px;

            position:relative;

            overflow:hidden;
        }

        /* PEMISAH ESTETIK */

        .right-side::before{

            content:'';

            position:absolute;

            left:-120px;
            top:-50px;

            width:260px;
            height:120%;

            background:#f7fbff;

            border-radius:50%;

            transform:rotate(8deg);

            box-shadow:
            -10px 0 30px rgba(0,0,0,.03);
        }

        .login-card{

            width:100%;
            max-width:390px;

            background:rgba(255,255,255,.92);

            backdrop-filter:blur(20px);

            border-radius:30px;

            padding:40px 35px;

            box-shadow:
            0 15px 45px rgba(0,0,0,.08);

            position:relative;
            z-index:5;
        }

        .login-logo{

            text-align:center;

            margin-bottom:18px;
        }

        .login-logo img{
            width:82px;
        }

        .login-title{

            text-align:center;

            font-size:30px;
            font-weight:800;

            color:#103b77;

            margin-bottom:8px;
        }

        .login-subtitle{

            text-align:center;

            color:#7d8ca3;

            font-size:14px;

            margin-bottom:32px;
        }

        .form-control,
        .form-select{

            height:57px;

            border-radius:16px;

            border:1px solid #d8e4f6;

            padding-left:18px;

            font-size:15px;

            margin-bottom:18px;
        }

        .form-control:focus,
        .form-select:focus{

            border-color:#2f80ed;

            box-shadow:
            0 0 0 4px rgba(47,128,237,.12);
        }

        .btn-login{

            width:100%;
            height:57px;

            border:none;
            border-radius:16px;

            background:
            linear-gradient(
            135deg,
            #0047c6,
            #2f80ed);

            color:white;

            font-size:17px;
            font-weight:700;

            transition:.3s;
        }

        .btn-login:hover{

            transform:translateY(-2px);

            box-shadow:
            0 10px 25px rgba(0,91,234,.22);
        }

        .footer-text{

            text-align:center;

            margin-top:24px;

            font-size:12px;

            color:#90a0b7;

            line-height:1.8;
        }

        .alert{

            border-radius:16px;
            font-size:14px;
        }

        /* =======================
           RESPONSIVE
        ======================= */

        @media(max-width:1200px){

            .hero-title{
                font-size:46px;
            }

        }

        @media(max-width:1100px){

            body{
                overflow:auto;
            }

            .main-wrapper{
                flex-direction:column;
                height:auto;
            }

            .left-side,
            .right-side{
                width:100%;
            }

            .right-side::before{
                display:none;
            }

            .left-side{

                min-height:750px;

                padding:45px 30px;
            }

            .hero-title{
                font-size:40px;
                white-space:normal;
            }

            .right-side{
                padding:50px 20px;
            }

        }

    </style>

</head>

<body>

<div class="main-wrapper">

    <!-- LEFT -->

    <div class="left-side">

        <div class="content">

            <img src="../assets/logo.png" class="main-logo">

            <div class="hero-title">
                SELAMAT DATANG !
            </div>

            <div class="hero-subtitle">
                di Sistem Informasi Manajemen Kajian Masjid Muhammadiyah
            </div>

            <div class="simakmu-title">
                SIMAKMU
            </div>

            <div class="line"></div>

            <div class="hero-desc">

                Sistem informasi ini dirancang untuk membantu pengelolaan
                kegiatan dan profil masjid, data takmir, dan data mubaligh
                supaya lebih terstruktur.

            </div>

            <div class="ayat-box">

                <p>
                    “Sesungguhnya yang memakmurkan masjid-masjid Allah
                    hanyalah orang-orang yang beriman kepada Allah
                    dan hari kemudian.”
                </p>

                <span>
                    (QS. At-Taubah : 18)
                </span>

            </div>

        </div>

    </div>

    <!-- RIGHT -->

    <div class="right-side">

        <div class="login-card">

            

            <div class="login-title">
                LOGIN
            </div>

            <div class="login-subtitle">
                Silakan login sesuai role dan akun
            </div>

            <?php if ($error) { ?>

                <div class="alert alert-danger text-center mb-3">

                    <b>LOGIN GAGAL!</b><br>

                    Periksa kembali Email, Password atau Role Anda

                </div>

            <?php } ?>

            <form method="POST" autocomplete="off">

                <select name="role" class="form-select" required>

                    <option value="">Login Sebagai</option>

                    <option value="admin">Admin</option>

                    <option value="takmir">Takmir</option>

                    <option value="mubaligh">Mubaligh</option>

                </select>

                <input type="email"
                    name="email"
                    class="form-control"
                    placeholder="Masukkan Email"
                    required
                    autocomplete="off">

                <input type="password"
                    name="password"
                    class="form-control"
                    placeholder="Password"
                    required
                    autocomplete="new-password">

                <button type="submit" name="login" class="btn-login">

                    Login

                </button>

            </form>

            <div class="footer-text">

                © 2026 SIMAKMU
                <p>Masjid Muhammadiyah</p>

            </div>

        </div>

    </div>

</div>

<script>

window.addEventListener('load', function () {

    document.querySelector('form').reset();

});

</script>

</body>
</html>