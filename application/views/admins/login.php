<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>เข้าสู่ระบบผู้ดูแล | Science & Technology Training</title>
    <link rel="icon" href="<?= base_url('assets/images/logos/iconsci-removebg.png'); ?>" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css?family=Chakra+Petch|Sarabun|Kodchasan" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        <?php include APPPATH.'views/admins/layouts/admin.css'; ?>

        .admin-login {
            position: relative;
            overflow-x: hidden;
            color: #1f2937;
            font-family: "Chakra Petch", sans-serif;
            background:
                radial-gradient(circle at 12% 16%, rgba(255, 183, 93, .34), transparent 26rem),
                radial-gradient(circle at 86% 12%, rgba(31, 138, 112, .32), transparent 24rem),
                linear-gradient(135deg, rgba(33, 37, 41, .98), rgba(36, 93, 143, .9) 56%, rgba(31, 138, 112, .88));
        }

        .admin-login button,
        .admin-login input,
        .admin-login select,
        .admin-login textarea {
            font-family: "Chakra Petch", sans-serif;
        }

        .admin-login::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            background-image:
                linear-gradient(rgba(255, 255, 255, .08) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, .08) 1px, transparent 1px);
            background-size: 42px 42px;
            mask-image: linear-gradient(120deg, rgba(0, 0, 0, .75), transparent 70%);
        }

        .admin-login .container {
            position: relative;
            z-index: 1;
        }

        .admin-login__brand img {
            width: 76px;
            padding: .45rem;
            border-radius: 1rem;
            background: rgba(255, 255, 255, .95);
            box-shadow: 0 16px 40px rgba(0, 0, 0, .18);
        }

        .admin-login h1 {
            max-width: 720px;
            font-size: clamp(1.75rem, 3.5vw, 2.5rem);
            line-height: 1.12;
            text-wrap: balance;
        }

        .admin-login h2 {
            font-size: 1.35rem;
        }

        .admin-login__brand {
            width: 100%;
            max-width: 100%;
            padding: .85rem 1rem;
            border: 1px solid rgba(255, 255, 255, .24);
            border-radius: 1.1rem;
            background: rgba(255, 255, 255, .1);
            backdrop-filter: blur(12px);
            box-shadow: 0 18px 44px rgba(0, 0, 0, .16);
            transition: border-color .2s ease, background .2s ease, transform .2s ease, box-shadow .2s ease;
        }

        .admin-login__brand:hover,
        .admin-login__brand:focus {
            border-color: rgba(255, 183, 93, .72);
            background: rgba(255, 255, 255, .16);
            box-shadow: 0 22px 54px rgba(0, 0, 0, .22);
            transform: translateY(-1px);
        }

        .admin-login__brand strong {
            font-size: 1.5rem;
            line-height: 1.25;
        }

        .admin-login__brand span span {
            font-size: 1rem;
        }

        .admin-login,
        .admin-login .form-control,
        .admin-login .btn,
        .admin-login .form-label,
        .admin-login p {
            font-size: .95rem;
        }

        .admin-login .form-label {
            margin-bottom: .4rem;
        }

        .admin-login__stat {
            border-radius: 1rem;
            background: rgba(255, 255, 255, .13);
            backdrop-filter: blur(12px);
            box-shadow: 0 18px 46px rgba(0, 0, 0, .12);
        }

        .admin-login__stat strong {
            font-size: 1.15rem !important;
        }

        .admin-login__stat span {
            font-size: .9rem;
            line-height: 1.55;
        }

        .admin-login section[aria-label="เข้าสู่ระบบผู้ดูแล"] > .card {
            border-radius: 1.25rem !important;
            background: rgba(255, 255, 255, .96);
            box-shadow: 0 28px 70px rgba(0, 0, 0, .28) !important;
        }

        .admin-login .badge {
            padding: .45rem .7rem;
            border-radius: 999px;
            color: #3b2a12 !important;
            font-size: .78rem;
            letter-spacing: .02em;
        }

        .admin-login .form-control {
            min-height: 46px;
            border-color: #dbe3ec;
            border-radius: .8rem;
            background: #f8fafc;
        }

        .admin-login .form-control:focus {
            border-color: #ff8b03;
            background: #fff;
            box-shadow: 0 0 0 .25rem rgba(255, 139, 3, .18);
        }

        .admin-login .btn-primary {
            min-height: 46px;
            border: 0;
            border-radius: .8rem;
            background: linear-gradient(135deg, #ff8b03, #245d8f);
            font-weight: 800;
            box-shadow: 0 14px 28px rgba(36, 93, 143, .25);
        }

        .admin-login .btn-primary:hover,
        .admin-login .btn-primary:focus {
            background: linear-gradient(135deg, #e97b00, #1f4f79);
            transform: translateY(-1px);
        }

        .admin-login .alert {
            border-radius: .9rem;
        }

        @media (max-width: 991.98px) {
            .admin-login .container {
                align-items: flex-start !important;
            }

            .admin-login h1 {
                font-size: clamp(1.65rem, 7vw, 2.2rem);
            }
        }
    </style>
</head>
<body class="admin-login">
    <main class="container min-vh-100 d-flex align-items-center py-4 py-lg-5">
        <div class="row align-items-center g-4 g-lg-5 w-100 mx-0">
            <section class="col-lg-7 text-white" aria-label="ระบบผู้ดูแล">

                <div class="row">
                    <div class="col-12">
                        <a class="admin-login__brand d-flex align-items-center gap-3 mb-4 text-white text-decoration-none" href="<?= base_url('index.php'); ?>">
                            <img src="<?= base_url('assets/images/logos/aru-logo-h90.png'); ?>" alt="ตรามหาวิทยาลัยราชภัฏพระนครศรีอยุธยา">
                            <span>
                                <strong class="d-block">Science & Technology Training</strong>
                                <span class="d-block text-warning">คณะวิทยาศาสตร์และเทคโนโลยี</span>
                            </span>
                        </a>
                    </div>
                </div>

                <h1 class="fw-bold text-white mb-3">ระบบจัดการข้อมูลการอบรม</h1>
                <p class="text-white-50 mb-4">สำหรับผู้ดูแลระบบ ใช้จัดการหลักสูตร ผู้สมัคร และข้อมูลสำคัญของศูนย์อบรมให้เป็นระเบียบและพร้อมใช้งาน</p>

                <div class="row g-3" aria-label="ภาพรวมระบบ">
                    <div class="col-sm-6">
                        <div class="admin-login__stat card border text-white h-100">
                            <div class="card-body">
                                <strong class="d-block text-warning">Admin</strong>
                                <span class="text-white-50">จัดการข้อมูลหลักสูตรและข่าวสาร</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="admin-login__stat card border text-white h-100">
                            <div class="card-body">
                                <strong class="d-block text-warning">Secure</strong>
                                <span class="text-white-50">เข้าสู่ระบบด้วยบัญชีผู้ดูแลเท่านั้น</span>
                            </div>
                        </div>
                    </div>
                </div>

            </section>

            <section class="col-lg-5" aria-label="เข้าสู่ระบบผู้ดูแล">
                <div class="card border-0 shadow-lg rounded-3">
                    <div class="card-body p-4 p-lg-5">
                        <span class="badge text-bg-warning mb-3">Administrator</span>
                        <h2 class="h3 mb-2">เข้าสู่ระบบผู้ดูแล</h2>
                        <p class="text-secondary mb-4">กรอกชื่อผู้ใช้และรหัสผ่านเพื่อเข้าสู่แผงควบคุม</p>

                        <?php if ($this->session->flashdata('error')): ?>
                            <div class="alert alert-danger fw-semibold" role="alert">
                                <?= $this->session->flashdata('error'); ?>
                            </div>
                        <?php endif; ?>

                        <form class="row g-3" method="post" action="<?= site_url('admin/check-login'); ?>">
                            <div class="col-12">
                                <label class="form-label fw-bold" for="username">Username</label>
                                <input class="form-control" id="username" type="text" name="username" autocomplete="username" placeholder="กรอกชื่อผู้ใช้" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold" for="password">Password</label>
                                <input class="form-control" id="password" type="password" name="password" autocomplete="current-password" placeholder="กรอกรหัสผ่าน" required>
                            </div>

                            <div class="col-12">
                                <button class="btn btn-primary w-100" type="submit">เข้าสู่ระบบ</button>
                            </div>
                        </form>

                        <div class="border-top mt-4 pt-4 text-center text-secondary fw-semibold">
                            Training Management System
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
