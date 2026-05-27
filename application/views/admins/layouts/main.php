<?php
$admin_name = $this->session->userdata('admin_name') ?: 'ผู้ดูแลระบบ';
$admin_role = $this->session->userdata('admin_role') ?: 'Administrator';
?>
        <div class="admin-content">
            <header class="admin-topbar d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 px-3 px-lg-4 py-3 border-bottom">
                <div>
                    <h1 class="h4 mb-1">แดชบอร์ดผู้ดูแลระบบ</h1>
                    <p class="mb-0 text-secondary">ภาพรวมการจัดการข้อมูลการอบรม</p>
                </div>
                <a class="btn btn-dark" href="<?= site_url('admin/logout'); ?>">ออกจากระบบ</a>
            </header>

            <main class="admin-main py-4">
                <section class="admin-hero card border-0 rounded-3 shadow-sm text-white position-relative mb-4">
                    <div class="card-body d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4 p-4 p-lg-5">
                        <div>
                            <p class="mb-2 fw-bold text-warning">ยินดีต้อนรับ, <?= html_escape($admin_name); ?></p>
                            <h2 class="display-6 fw-bold text-white mb-3">จัดการระบบอบรมของคณะได้จากศูนย์กลางเดียว</h2>
                            <p class="mb-0 text-white-50">ติดตามหลักสูตร ผู้สมัคร รายงาน และสถานะการดำเนินงานสำคัญของระบบ Training Management</p>
                        </div>
                        <a class="btn btn-warning fw-bold text-nowrap" href="#">เพิ่มหลักสูตร</a>
                    </div>
                </section>

                <section class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3 mb-4" aria-label="สรุปข้อมูลระบบ">
                    <div class="col">
                        <article class="admin-stat-card card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">หลักสูตรทั้งหมด</span>
                                <strong class="d-block fs-2 lh-1 my-2">24</strong>
                                <p class="mb-0 text-secondary small">8 หลักสูตรเปิดรับสมัคร</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-blue card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">ผู้สมัครสะสม</span>
                                <strong class="d-block fs-2 lh-1 my-2">1,250</strong>
                                <p class="mb-0 text-secondary small">เพิ่มขึ้น 74 รายในวันนี้</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-green card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">รออนุมัติ</span>
                                <strong class="d-block fs-2 lh-1 my-2">18</strong>
                                <p class="mb-0 text-secondary small">รายการสมัครที่ต้องตรวจสอบ</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-red card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">สิทธิ์ผู้ใช้</span>
                                <strong class="d-block fs-4 lh-sm my-2"><?= html_escape($admin_role); ?></strong>
                                <p class="mb-0 text-secondary small">บัญชีสำหรับจัดการระบบ</p>
                            </div>
                        </article>
                    </div>
                </section>

                <section class="row g-4">
                    <div class="col-lg-8">
                        <article class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white d-flex align-items-center justify-content-between gap-3">
                                <h3 class="h5 mb-0">รายการอบรมล่าสุด</h3>
                                <a class="fw-bold" href="#">ดูทั้งหมด</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>หลักสูตร</th>
                                                <th>วันที่อบรม</th>
                                                <th>ผู้สมัคร</th>
                                                <th>สถานะ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>การวิเคราะห์ข้อมูลด้วย Python</td>
                                                <td>18-19 มิ.ย. 2569</td>
                                                <td>30 คน</td>
                                                <td><span class="badge text-bg-success">เปิดรับสมัคร</span></td>
                                            </tr>
                                            <tr>
                                                <td>มาตรฐานห้องปฏิบัติการและความปลอดภัย</td>
                                                <td>25 มิ.ย. 2569</td>
                                                <td>24 คน</td>
                                                <td><span class="badge text-bg-success">เปิดรับสมัคร</span></td>
                                            </tr>
                                            <tr>
                                                <td>สร้างสื่อดิจิทัลสำหรับงานอบรม</td>
                                                <td>2 ก.ค. 2569</td>
                                                <td>42 คน</td>
                                                <td><span class="badge text-bg-info">ออนไลน์</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div class="col-lg-4">
                        <aside class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white">
                                <h3 class="h5 mb-0">งานที่ควรติดตาม</h3>
                            </div>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <strong class="d-block">ตรวจสอบรายชื่อผู้สมัครใหม่</strong>
                                    <span class="small text-secondary">18 รายการรอการยืนยันข้อมูล</span>
                                </li>
                                <li class="list-group-item">
                                    <strong class="d-block">อัปเดตข้อมูลหลักสูตร</strong>
                                    <span class="small text-secondary">เพิ่มรายละเอียดรอบอบรมเดือนถัดไป</span>
                                </li>
                                <li class="list-group-item">
                                    <strong class="d-block">สรุปรายงานการอบรม</strong>
                                    <span class="small text-secondary">เตรียมข้อมูลสำหรับผู้บริหารคณะ</span>
                                </li>
                            </ul>
                        </aside>
                    </div>
                </section>
            </main>
