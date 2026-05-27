<?php
$admin_name = $this->session->userdata('admin_name') ?: 'ผู้ดูแลระบบ';
$admin_role = $this->session->userdata('admin_role') ?: 'Administrator';
$initial = function_exists('mb_substr') ? mb_substr($admin_name, 0, 1, 'UTF-8') : substr($admin_name, 0, 1);
$current_uri = $this->uri->uri_string();
?>
        <aside class="admin-sidebar p-3 p-lg-4 bg-admin-sidebar" aria-label="เมนูผู้ดูแลระบบ">
            <a class="admin-brand d-flex align-items-center gap-3 pb-3 border-bottom border-white border-opacity-10" href="<?= site_url('admin/dashboard'); ?>">
                <img src="<?= base_url('assets/images/logos/aru-logo-h90.png'); ?>" alt="ตรามหาวิทยาลัยราชภัฏพระนครศรีอยุธยา">
                <span>
                    <strong class="d-block">Training Admin</strong>
                    <small class="d-block fw-semibold">Science & Technology</small>
                </span>
            </a>

            <div class="admin-user my-4 p-3 rounded-3 text-center">
                <div class="admin-avatar mx-auto mb-2"><?= html_escape($initial); ?></div>
                <strong class="d-block text-break"><?= html_escape($admin_name); ?></strong>
                <span class="small text-white-50"><?= html_escape($admin_role); ?></span>
            </div>

            <nav class="admin-menu nav nav-pills flex-column gap-2">
                <a class="nav-link <?= $current_uri === 'admin/dashboard' ? 'active' : ''; ?>" href="<?= site_url('admin/dashboard'); ?>">Dashboard</a>
                <a class="nav-link <?= strpos($current_uri, 'admin/admins') === 0 ? 'active' : ''; ?>" href="<?= site_url('admin/admins'); ?>">จัดการผู้ดูแลระบบ</a>
                <a class="nav-link <?= (strpos($current_uri, 'admin/categories') === 0 || strpos($current_uri, 'admin/courses') === 0 || strpos($current_uri, 'admin/course-details') === 0 || strpos($current_uri, 'admin/course-instructors') === 0) ? 'active' : ''; ?>" href="<?= site_url('admin/courses'); ?>">หลักสูตรอบรม</a>
                <a class="nav-link <?= strpos($current_uri, 'admin/batches') === 0 ? 'active' : ''; ?>" href="<?= site_url('admin/batches'); ?>">จัดการรุ่นอบรม</a>
                <a class="nav-link" href="#">จัดการผู้ลงทะเบียน</a>
                <a class="nav-link <?= strpos($current_uri, 'admin/instructors') === 0 ? 'active' : ''; ?>" href="<?= site_url('admin/instructors'); ?>">จัดการวิทยากร</a>
                <a class="nav-link" href="#">จัดการเอกสาร</a>
                <a class="nav-link" href="#">รายงาน</a>
                <a class="nav-link admin-menu__logout mt-2" href="<?= site_url('admin/logout'); ?>">ออกจากระบบ</a>
            </nav>
        </aside>
