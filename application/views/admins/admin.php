<?php
$this->load->view('admins/layouts/header');
$this->load->view('admins/layouts/sidebar');

$admins = isset($admins) ? $admins : array();
$edit_admin = isset($edit_admin) && is_object($edit_admin) ? $edit_admin : (object) array(
    'id' => NULL,
    'name' => '',
    'username' => '',
    'role' => 'staff',
    'status' => 'active'
);
$roles = isset($roles) ? $roles : array('super_admin', 'admin', 'staff', 'viewer');
$statuses = isset($statuses) ? $statuses : array('active', 'inactive');
$stats = array_merge(
    array(
        'total' => 0,
        'active' => 0,
        'inactive' => 0,
        'super_admin' => 0
    ),
    isset($stats) && is_array($stats) ? $stats : array()
);

$is_edit = !empty($edit_admin->id);
$form_action = $is_edit
    ? site_url('admin/admins/update/'.$edit_admin->id)
    : site_url('admin/admins/store');
$role_labels = array(
    'super_admin' => 'Super Admin',
    'admin' => 'Admin',
    'staff' => 'Staff',
    'viewer' => 'Viewer'
);
$status_labels = array(
    'active' => 'ใช้งาน',
    'inactive' => 'ปิดใช้งาน'
);
?>
        <div class="admin-content">
            <header class="admin-topbar d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 px-3 px-lg-4 py-3 border-bottom">
                <div>
                    <h1 class="h4 mb-1">รายงานผู้ดูแลระบบ</h1>
                    <p class="mb-0 text-secondary">จัดการบัญชีผู้ดูแลระบบและสิทธิ์การใช้งาน</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <?php if ($is_edit): ?>
                        <a class="btn btn-outline-dark" href="<?= site_url('admin/admins'); ?>">ยกเลิกแก้ไข</a>
                    <?php endif; ?>
                    <a class="btn btn-dark" href="<?= site_url('admin/logout'); ?>">ออกจากระบบ</a>
                </div>
            </header>

            <main class="admin-main py-4">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success fw-semibold" role="status"><?= html_escape($this->session->flashdata('success')); ?></div>
                <?php endif; ?>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger fw-semibold" role="alert"><?= html_escape($this->session->flashdata('error')); ?></div>
                <?php endif; ?>

                <section class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3 mb-4" aria-label="สรุปผู้ดูแลระบบ">
                    <div class="col">
                        <article class="admin-stat-card card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">ผู้ดูแลทั้งหมด</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['total']); ?></strong>
                                <p class="mb-0 text-secondary small">บัญชีในระบบ</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-blue card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">ใช้งานอยู่</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['active']); ?></strong>
                                <p class="mb-0 text-secondary small">เข้าสู่ระบบได้</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-green card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">ปิดใช้งาน</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['inactive']); ?></strong>
                                <p class="mb-0 text-secondary small">ระงับการใช้งาน</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-red card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">Super Admin</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['super_admin']); ?></strong>
                                <p class="mb-0 text-secondary small">สิทธิ์สูงสุด</p>
                            </div>
                        </article>
                    </div>
                </section>

                <section class="row g-4">
                    <div class="col-xl-8">
                        <article class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white d-flex align-items-center justify-content-between gap-3">
                                <h3 class="h5 mb-0">รายการผู้ดูแลระบบ</h3>
                                <a class="fw-bold" href="<?= site_url('admin/admins'); ?>">รีเฟรช</a>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>ชื่อ</th>
                                                <th>Username</th>
                                                <th>สิทธิ์</th>
                                                <th>สถานะ</th>
                                                <th>จัดการ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($admins)): ?>
                                                <tr>
                                                    <td colspan="5" class="text-center text-secondary py-4">ยังไม่มีข้อมูลผู้ดูแลระบบ</td>
                                                </tr>
                                            <?php endif; ?>

                                            <?php foreach ($admins as $admin): ?>
                                                <tr>
                                                    <td><?= html_escape($admin->name); ?></td>
                                                    <td><?= html_escape($admin->username); ?></td>
                                                    <td><?= html_escape(isset($role_labels[$admin->role]) ? $role_labels[$admin->role] : $admin->role); ?></td>
                                                    <td>
                                                        <span class="badge <?= $admin->status === 'inactive' ? 'text-bg-secondary' : 'text-bg-success'; ?>">
                                                            <?= html_escape(isset($status_labels[$admin->status]) ? $status_labels[$admin->status] : $admin->status); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/admins?edit='.$admin->id); ?>">แก้ไข</a>
                                                            <?php if ((int) $admin->id !== (int) $this->session->userdata('admin_id')): ?>
                                                                <form class="js-admin-delete-form" method="post" action="<?= site_url('admin/admins/delete/'.$admin->id); ?>" data-admin-name="<?= html_escape($admin->name); ?>">
                                                                    <button class="btn btn-sm btn-outline-danger" type="submit">ลบ</button>
                                                                </form>
                                                            <?php endif; ?>
                                                        </div>
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
                        <aside class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h3 class="h5 mb-0"><?= $is_edit ? 'แก้ไขผู้ดูแล' : 'เพิ่มผู้ดูแล'; ?></h3>
                            </div>

                            <div class="card-body">
                                <form class="row g-3" method="post" action="<?= $form_action; ?>">
                                    <div class="col-12">
                                        <label class="form-label fw-bold" for="name">ชื่อผู้ดูแล</label>
                                        <input class="form-control" id="name" type="text" name="name" value="<?= $is_edit ? html_escape($edit_admin->name) : ''; ?>" required>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-bold" for="username">Username</label>
                                        <input class="form-control" id="username" type="text" name="username" value="<?= $is_edit ? html_escape($edit_admin->username) : ''; ?>" required>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-bold" for="password">Password</label>
                                        <input class="form-control" id="password" type="password" name="password" autocomplete="new-password" <?= $is_edit ? '' : 'required'; ?>>
                                        <?php if ($is_edit): ?>
                                            <div class="form-text">เว้นว่างไว้หากไม่ต้องการเปลี่ยนรหัสผ่าน</div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="col-md-6 col-xl-12">
                                        <label class="form-label fw-bold" for="role">สิทธิ์</label>
                                        <select class="form-select" id="role" name="role" required>
                                            <?php foreach ($roles as $role): ?>
                                                <?php $selected_role = $is_edit ? $edit_admin->role : 'staff'; ?>
                                                <option value="<?= html_escape($role); ?>" <?= $selected_role === $role ? 'selected' : ''; ?>>
                                                    <?= html_escape(isset($role_labels[$role]) ? $role_labels[$role] : $role); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6 col-xl-12">
                                        <label class="form-label fw-bold" for="status">สถานะ</label>
                                        <select class="form-select" id="status" name="status" required>
                                            <?php foreach ($statuses as $status): ?>
                                                <?php $selected_status = $is_edit ? $edit_admin->status : 'active'; ?>
                                                <option value="<?= html_escape($status); ?>" <?= $selected_status === $status ? 'selected' : ''; ?>>
                                                    <?= html_escape(isset($status_labels[$status]) ? $status_labels[$status] : $status); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <button class="btn btn-primary w-100" type="submit"><?= $is_edit ? 'บันทึกการแก้ไข' : 'เพิ่มผู้ดูแลระบบ'; ?></button>
                                    </div>
                                </form>
                            </div>
                        </aside>
                    </div>
                </section>
            </main>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.querySelectorAll('.js-admin-delete-form').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();

                    Swal.fire({
                        title: 'ยืนยันการลบผู้ดูแลระบบ?',
                        text: 'ต้องการลบ "' + form.dataset.adminName + '" ใช่หรือไม่',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'ลบข้อมูล',
                        cancelButtonText: 'ยกเลิก',
                        confirmButtonColor: '#d94f45',
                        reverseButtons: true
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        </script>
<?php $this->load->view('admins/layouts/footer'); ?>
