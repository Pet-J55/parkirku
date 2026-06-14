<?php
session_start();
include "../config/koneksi.php";

$error = "";

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = md5($_POST['password']);

    $query = mysqli_query($koneksi, "
        SELECT * FROM users 
        WHERE username = '$username' 
        AND password = '$password'
    ");

    $user = mysqli_fetch_assoc($query);

    if ($user) {
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        header("Location: ../dashboard.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ParkirKu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-primary: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --border: #e2e8f0;
            --radius-md: 12px;
            --radius-lg: 18px;
            --shadow-lg: 0 20px 40px -15px rgba(15, 23, 42, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 40%), 
                        radial-gradient(circle at bottom right, rgba(16, 185, 129, 0.05), transparent 40%),
                        #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-box {
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            padding: 40px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border);
            text-align: center;
            animation: fadeIn 0.5s ease forwards;
        }

        .logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 24px;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 24px;
        }

        .logo-icon {
            width: 44px;
            height: 44px;
            background: var(--primary);
            color: #ffffff;
            border-radius: var(--radius-md);
            display: grid;
            place-items: center;
            font-size: 20px;
            box-shadow: 0 8px 16px rgba(37, 99, 235, 0.25);
        }

        h2 {
            font-size: 22px;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        p {
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 30px;
        }

        .input-group {
            position: relative;
            margin-bottom: 16px;
            text-align: left;
        }

        .input-group label {
            display: block;
            font-weight: 700;
            font-size: 12px;
            color: var(--text-main);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 16px;
            transition: all 0.3s ease;
        }

        input {
            width: 100%;
            padding: 14px 16px 14px 46px;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font-family: inherit;
            font-size: 15px;
            outline: none;
            background: #f8fafc;
            color: var(--text-main);
            transition: all 0.3s ease;
        }

        input:focus {
            border-color: var(--primary);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        input:focus + i {
            color: var(--primary);
        }

        button {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: #ffffff;
            border: none;
            border-radius: var(--radius-md);
            font-family: inherit;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 24px rgba(37, 99, 235, 0.2);
            margin-top: 10px;
        }

        button:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(37, 99, 235, 0.3);
        }

        .error {
            background: #fee2e2;
            color: #b91c1c;
            padding: 12px 16px;
            border-radius: var(--radius-md);
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 600;
            border: 1px solid rgba(239, 68, 68, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 24px;
            text-align: center;
            color: var(--primary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .back:hover {
            color: var(--primary-dark);
            transform: translateX(-2px);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>

<div class="login-box">
    <div class="logo">
        <div class="logo-icon"><i class="fa-solid fa-square-parking"></i></div>
        <span>ParkirKu</span>
    </div>
    
    <h2>Selamat Datang</h2>
    <p>Silakan masuk untuk mengelola dashboard</p>

    <?php if ($error != "") { ?>
        <div class="error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span><?= $error; ?></span>
        </div>
    <?php } ?>

    <form method="POST">
        <div class="input-group">
            <label>Username</label>
            <div class="input-wrapper">
                <input type="text" name="username" placeholder="Masukkan username Anda" required autocomplete="username">
                <i class="fa-solid fa-user"></i>
            </div>
        </div>

        <div class="input-group">
            <label>Password</label>
            <div class="input-wrapper">
                <input type="password" name="password" placeholder="Masukkan password Anda" required autocomplete="current-password">
                <i class="fa-solid fa-lock"></i>
            </div>
        </div>

        <button type="submit" name="login">
            Masuk Sistem <i class="fa-solid fa-arrow-right-to-bracket" style="margin-left: 6px;"></i>
        </button>
    </form>

    <a href="../../index.html" class="back">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Landing Page
    </a>
</div>

</body>
</html>