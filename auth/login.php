<?php
require_once '../config/koneksi.php';
require_once '../config/constants.php';
session_start();

// Redirect jika sudah login
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    header("Location: " . BASE_URL . "/modules/" . $_SESSION['role'] . "/dashboard.php");
    exit;
}

// Proses login jika form dikirim
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Username dan Password wajib diisi.";
    } else {
        $stmt = $conn->prepare("SELECT id, nama, username, password, role FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Catatan: gunakan password_verify jika sudah hash sementara saat pengujian tidak di hash
            if ($password === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = $user['role'];

                header("Location: " . BASE_URL . "/modules/" . $user['role'] . "/dashboard.php");
                exit;
            } else {
                $error = "Password salah.";
            }
        } else {
            $error = "Username tidak ditemukan.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Login - Perjalanan Dinas</title>
    <link rel="icon" href="<?= BASE_URL ?>/assets/images/balai/PUPR.png" type="image/x-icon">

    <!-- Bootstrap CSS -->
    <link href="<?= BASE_URL ?>/assets/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #1e3a8a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card p-4">
                    <div class="text-center mb-3">
                        <img src="<?= BASE_URL ?>/assets/images/balai/PUPR.png" alt="Logo" width="80" class="mb-3">
                        <h4 class="fw-bold">Login</h4>
                    </div>
                    <?php if ($error): ?>
                        <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa fa-user-tie"></i></span>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan Username" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa fa-key"></i></span>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan Password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                    <i id="toggleIcon" class="fas fa-eye-slash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary fw-medium">Masuk</button>
                        </div>
                        <div class="text-center">
                            <a href="<?= BASE_URL ?>/auth/forgot-password.php" class="d-block mb-1 text-decoration-none">Lupa password?</a>
                            <span>Belum punya akun? Silahkan <a href="<?= BASE_URL ?>/auth/register.php" class="text-decoration-none">daftar</a></span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="<?= BASE_URL ?>/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Toggle Password Script -->
    <script>
        function togglePassword() {
            const password = document.getElementById("password");
            const toggleIcon = document.getElementById("toggleIcon");
            if (password.type === "password") {
                password.type = "text";
                toggleIcon.classList.remove("fa-eye-slash");
                toggleIcon.classList.add("fa-eye");
            } else {
                password.type = "password";
                toggleIcon.classList.remove("fa-eye");
                toggleIcon.classList.add("fa-eye-slash");
            }
        }
    </script>

</body>

</html>