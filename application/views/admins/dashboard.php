<?php
$this->load->view('admins/layouts/header');
$this->load->view('admins/layouts/sidebar');

$admin_name = $this->session->userdata('admin_name') ?: 'ผู้ดูแลระบบ';
$admin_role = $this->session->userdata('admin_role') ?: 'Administrator';
$overview = array_merge(array(
    'registrations' => 0,
    'pending_registrations' => 0,
    'participants' => 0,
    'revenue_uploaded' => 0,
    'pending_slips' => 0
), isset($overview) && is_array($overview) ? $overview : array());
$course_report = isset($course_report) && is_array($course_report) ? array_slice($course_report, 0, 5) : array();
$payment_report = isset($payment_report) && is_array($payment_report) ? $payment_report : array();
$recent_registrations = isset($recent_registrations) && is_array($recent_registrations) ? $recent_registrations : array();
$tables_ready = isset($tables_ready) ? (bool) $tables_ready : FALSE;
$registration_labels = array(
    1 => 'รอชำระเงิน / รออนุมัติ',
    2 => 'อนุมัติแล้ว',
    3 => 'ไม่อนุมัติ',
    4 => 'ยกเลิก',
    5 => 'เข้าอบรมแล้ว'
);
$registration_badges = array(
    1 => 'text-bg-warning',
    2 => 'text-bg-success',
    3 => 'text-bg-danger',
    4 => 'text-bg-secondary',
    5 => 'text-bg-info'
);
$payment_labels = array(
    1 => 'รอชำระเงิน',
    2 => 'รอตรวจสอบสลิป',
    3 => 'ชำระเงินแล้ว',
    4 => 'ไม่ผ่านการตรวจสอบ'
);
$format_money = function ($amount) {
    return number_format((float) $amount, 2).' บาท';
};
$today_label = date('d/m/').((int) date('Y') + 543);
?>
        <style>
            .admin-dashboard-page{--dashboard-ink:#26364b;--dashboard-line:#e7edf4;color:var(--dashboard-ink);font-size:.9rem}
            .admin-dashboard-page .admin-main{padding-top:1.25rem!important}
            .admin-dashboard-page .admin-hero{border-radius:1rem!important;box-shadow:0 .6rem 1.8rem rgba(24,51,82,.12)!important}
            .admin-dashboard-page .admin-hero .card-body{padding:1.75rem!important}
            .admin-dashboard-page .admin-hero p:first-child{font-size:.88rem}
            .admin-dashboard-page .admin-hero h2{font-size:clamp(1.4rem,2.2vw,2rem);line-height:1.35;letter-spacing:-.015em}
            .admin-dashboard-page .admin-hero p:not(:first-child){max-width:42rem;font-size:.88rem;line-height:1.7}
            .admin-dashboard-page .admin-stat-card{border-radius:.8rem;box-shadow:0 .25rem .9rem rgba(32,52,77,.06)!important}
            .admin-dashboard-page .admin-stat-card .card-body{padding:.95rem 1rem}
            .admin-dashboard-page .admin-stat-card strong{margin:.35rem 0!important;font-size:1.65rem!important;color:#263e5d}
            .admin-dashboard-page .admin-stat-card span,.admin-dashboard-page .admin-stat-card p{font-size:.76rem!important}
            .admin-dashboard-page .card{border-radius:.8rem}
            .admin-dashboard-page .card-header{padding:.85rem 1rem;border-bottom-color:var(--dashboard-line)}
            .admin-dashboard-page .card-header h3{font-size:.98rem;font-weight:700;color:#263e5d}
            .admin-dashboard-page .table{font-size:.8rem;color:var(--dashboard-ink)}
            .admin-dashboard-page .table thead th{padding:.7rem .65rem;white-space:nowrap;background:#f6f8fb;color:#52647b;font-size:.72rem;font-weight:700;border-bottom-color:var(--dashboard-line)}
            .admin-dashboard-page .table tbody td{padding:.7rem .65rem;line-height:1.5;vertical-align:middle;border-color:#edf1f5}
            .admin-dashboard-page .table tbody tr:hover{background:#f8fbff}
            .admin-dashboard-page .table tbody td strong{color:#455468;font-weight:500}
            .admin-dashboard-page .table .badge,.admin-dashboard-page .list-group .badge{font-size:.68rem;font-weight:600}
            .admin-dashboard-page .list-group-item{padding:.85rem 1rem;border-color:#edf1f5;font-size:.82rem}
            .admin-dashboard-page .list-group-item strong{font-weight:600;color:#40536c}
            .admin-dashboard-page .list-group-item .small{font-size:.74rem}
            .admin-dashboard-page .alert{border:0;border-radius:.7rem;font-size:.84rem}
            @media(max-width:767.98px){.admin-dashboard-page .admin-main{padding-right:.75rem;padding-left:.75rem}.admin-dashboard-page .admin-hero .card-body{padding:1.25rem!important}.admin-dashboard-page .admin-stat-card .card-body{padding:.8rem}.admin-dashboard-page .table tbody td{padding:.6rem .55rem}}
        </style>
        <div class="admin-content">
            <header class="admin-topbar d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 px-3 px-lg-4 py-3 border-bottom">
                <div>
                    <h1 class="h4 mb-1">Dashboard</h1>
                    <p class="mb-0 text-secondary">ภาพรวมระบบอบรมและงานที่ต้องติดตาม</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-primary" href="<?= site_url('admin/reports'); ?>">ดูรายงาน</a>
                    <a class="btn btn-dark" href="<?= site_url('admin/logout'); ?>">ออกจากระบบ</a>
                </div>
            </header>

            <main class="admin-main py-4 admin-dashboard-page">
                <?php if (!$tables_ready): ?>
                    <div class="alert alert-warning fw-semibold" role="alert">
                        ยังไม่พบตารางข้อมูลลงทะเบียน กรุณารัน SQL ของระบบก่อนใช้งาน Dashboard เต็มรูปแบบ
                    </div>
                <?php endif; ?>

                <section class="admin-hero card border-0 rounded-3 shadow-sm text-white position-relative mb-4">
                    <div class="card-body d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-4 p-4 p-lg-5">
                        <div>
                            <p class="mb-2 fw-bold text-warning">ยินดีต้อนรับ, <?= html_escape($admin_name); ?></p>
                            <h2 class="display-6 fw-bold text-white mb-3">ศูนย์ควบคุมงานอบรมคณะวิทยาศาสตร์</h2>
                            <p class="mb-0 text-white-50">ติดตามใบสมัคร รายชื่อผู้เข้าอบรม การอัปโหลดสลิป และรายงานการดำเนินงานได้จากหน้าเดียว</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a class="btn btn-warning fw-bold" href="<?= site_url('admin/registrations'); ?>">ตรวจผู้ลงทะเบียน</a>
                            <a class="btn btn-light fw-bold" href="<?= site_url('admin/batches'); ?>">จัดการรุ่นอบรม</a>
                        </div>
                    </div>
                </section>

                <section class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3 mb-4" aria-label="สรุปข้อมูลระบบ">
                    <div class="col">
                        <article class="admin-stat-card card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">ใบสมัครทั้งหมด</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $overview['registrations']); ?></strong>
                                <p class="mb-0 text-secondary small">ข้อมูลสะสมในระบบ</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-green card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">ผู้เข้าอบรม</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $overview['participants']); ?></strong>
                                <p class="mb-0 text-secondary small">รายชื่อ active ทั้งหมด</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-blue card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">ยอดสลิปที่อัปโหลด</span>
                                <strong class="d-block fs-4 lh-sm my-2"><?= html_escape($format_money($overview['revenue_uploaded'])); ?></strong>
                                <p class="mb-0 text-secondary small">รอตรวจสอบและชำระแล้ว</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-yellow card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">รอตรวจสลิป</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $overview['pending_slips']); ?></strong>
                                <p class="mb-0 text-secondary small">รายการ payment status 2</p>
                            </div>
                        </article>
                    </div>
                </section>

                <section class="row g-4">
                    <div class="col-xl-8">
                        <article class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white d-flex align-items-center justify-content-between gap-3">
                                <div>
                                    <h3 class="h5 mb-0">รายการสมัครล่าสุด</h3>
                                    <span class="small text-secondary">อัปเดตวันที่ <?= html_escape($today_label); ?></span>
                                </div>
                                <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/registrations'); ?>">ดูทั้งหมด</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>เลขที่สมัคร</th>
                                                <th>ผู้สมัคร</th>
                                                <th>หลักสูตร</th>
                                                <th>ผู้เข้าอบรม</th>
                                                <th>สถานะ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($recent_registrations)): ?>
                                                <tr><td colspan="5" class="text-center text-secondary py-4">ยังไม่มีรายการสมัคร</td></tr>
                                            <?php endif; ?>
                                            <?php foreach ($recent_registrations as $registration): ?>
                                                <?php
                                                $status = (int) $registration->status;
                                                $member_name = trim($registration->title_name.$registration->first_name.' '.$registration->last_name);
                                                ?>
                                                <tr>
                                                    <td>
                                                        <a class="fw-bold" href="<?= site_url('admin/registrations/view/'.(int) $registration->id); ?>"><?= html_escape($registration->registration_code); ?></a>
                                                        <span class="d-block small text-secondary"><?= !empty($registration->created_at) ? html_escape(date('Y-m-d H:i', strtotime($registration->created_at))) : '-'; ?></span>
                                                    </td>
                                                    <td>
                                                        <strong class="d-block"><?= html_escape($member_name !== '' ? $member_name : '-'); ?></strong>
                                                        <span class="small text-secondary"><?= html_escape($registration->email ?: '-'); ?></span>
                                                    </td>
                                                    <td>
                                                        <?= html_escape($registration->course_title); ?>
                                                        <span class="d-block small text-secondary"><?= html_escape($registration->batch_no ?: '-'); ?></span>
                                                    </td>
                                                    <td><?= number_format((int) $registration->participant_count); ?> คน</td>
                                                    <td>
                                                        <span class="badge <?= isset($registration_badges[$status]) ? $registration_badges[$status] : 'text-bg-secondary'; ?>">
                                                            <?= html_escape(isset($registration_labels[$status]) ? $registration_labels[$status] : $status); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div class="col-xl-4">
                        <aside class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white">
                                <h3 class="h5 mb-0">งานที่ควรติดตาม</h3>
                            </div>
                            <div class="list-group list-group-flush">
                                <a class="list-group-item list-group-item-action d-flex justify-content-between gap-3" href="<?= site_url('admin/registrations?status=1'); ?>">
                                    <span>
                                        <strong class="d-block">ตรวจใบสมัครรออนุมัติ</strong>
                                        <span class="small text-secondary">รายการที่ต้องตรวจสถานะและข้อมูลผู้สมัคร</span>
                                    </span>
                                    <span class="badge text-bg-warning align-self-start"><?= number_format((int) $overview['pending_registrations']); ?></span>
                                </a>
                                <a class="list-group-item list-group-item-action d-flex justify-content-between gap-3" href="<?= site_url('admin/registrations'); ?>">
                                    <span>
                                        <strong class="d-block">ตรวจสลิปชำระเงิน</strong>
                                        <span class="small text-secondary">รายการที่ผู้สมัครอัปโหลดสลิปแล้ว</span>
                                    </span>
                                    <span class="badge text-bg-primary align-self-start"><?= number_format((int) $overview['pending_slips']); ?></span>
                                </a>
                                <a class="list-group-item list-group-item-action" href="<?= site_url('admin/reports'); ?>">
                                    <strong class="d-block">สรุปรายงานผู้เข้าอบรม</strong>
                                    <span class="small text-secondary">ดูยอดรวมและรายงานตามหลักสูตร</span>
                                </a>
                            </div>
                        </aside>
                    </div>

                    <div class="col-xl-7">
                        <article class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center gap-3">
                                <h3 class="h5 mb-0">หลักสูตรที่มีผู้สมัครสูงสุด</h3>
                                <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/reports'); ?>">รายงาน</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>หลักสูตร</th>
                                                <th>ใบสมัคร</th>
                                                <th>ผู้เข้าอบรม</th>
                                                <th>ยอดสลิป</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($course_report)): ?>
                                                <tr><td colspan="4" class="text-center text-secondary py-4">ยังไม่มีข้อมูลหลักสูตร</td></tr>
                                            <?php endif; ?>
                                            <?php foreach ($course_report as $row): ?>
                                                <tr>
                                                    <td>
                                                        <strong class="d-block"><?= html_escape($row->course_title); ?></strong>
                                                        <span class="small text-secondary"><?= html_escape($row->category_name ?: '-'); ?></span>
                                                    </td>
                                                    <td><?= number_format((int) $row->registration_count); ?></td>
                                                    <td><?= number_format((int) $row->participant_count); ?></td>
                                                    <td><?= html_escape($format_money($row->uploaded_amount)); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div class="col-xl-5">
                        <article class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white">
                                <h3 class="h5 mb-0">สถานะการชำระเงิน</h3>
                            </div>
                            <div class="card-body">
                                <?php if (empty($payment_report)): ?>
                                    <div class="alert alert-secondary mb-0">ยังไม่มีข้อมูลการชำระเงิน</div>
                                <?php endif; ?>
                                <div class="d-grid gap-3">
                                    <?php foreach ($payment_report as $row): ?>
                                        <?php $payment_status = (int) $row->status; ?>
                                        <div class="border rounded-3 p-3">
                                            <div class="d-flex justify-content-between align-items-start gap-3">
                                                <div>
                                                    <strong class="d-block"><?= html_escape(isset($payment_labels[$payment_status]) ? $payment_labels[$payment_status] : 'สถานะ '.$payment_status); ?></strong>
                                                    <span class="small text-secondary"><?= number_format((int) $row->payment_count); ?> รายการ</span>
                                                </div>
                                                <strong class="text-primary"><?= html_escape($format_money($row->total_amount)); ?></strong>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </article>
                    </div>
                </section>
            </main>

<?php $this->load->view('admins/layouts/footer'); ?>
