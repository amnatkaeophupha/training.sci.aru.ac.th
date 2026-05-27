<?php
$this->load->view('admins/layouts/header');
$this->load->view('admins/layouts/sidebar');

$instructors = isset($instructors) ? $instructors : array();
$edit_instructor = isset($edit_instructor) && is_object($edit_instructor) ? $edit_instructor : (object) array(
    'id' => NULL,
    'name' => '',
    'position' => '',
    'department' => '',
    'email' => '',
    'phone' => '',
    'photo' => '',
    'bio' => '',
    'is_active' => 1
);
$stats = array_merge(
    array(
        'total' => 0,
        'active' => 0,
        'inactive' => 0
    ),
    isset($stats) && is_array($stats) ? $stats : array()
);

$is_edit = !empty($edit_instructor->id);
$form_action = $is_edit
    ? site_url('admin/instructors/update/'.$edit_instructor->id)
    : site_url('admin/instructors/store');
?>
        <div class="admin-content">
            <header class="admin-topbar d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 px-3 px-lg-4 py-3 border-bottom">
                <div>
                    <h1 class="h4 mb-1">จัดการวิทยากร</h1>
                    <p class="mb-0 text-secondary">จัดเก็บข้อมูลวิทยากร ช่องทางติดต่อ รูปภาพ และประวัติความเชี่ยวชาญ</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <?php if ($is_edit): ?>
                        <a class="btn btn-outline-dark" href="<?= site_url('admin/instructors'); ?>">ยกเลิกแก้ไข</a>
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

                <section class="row row-cols-1 row-cols-md-3 g-3 mb-4" aria-label="สรุปวิทยากร">
                    <div class="col">
                        <article class="admin-stat-card card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">วิทยากรทั้งหมด</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['total']); ?></strong>
                                <p class="mb-0 text-secondary small">รายการในระบบ</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-green card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">ใช้งาน</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['active']); ?></strong>
                                <p class="mb-0 text-secondary small">พร้อมแสดงผล</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-red card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">ปิดใช้งาน</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['inactive']); ?></strong>
                                <p class="mb-0 text-secondary small">ซ่อนไว้ชั่วคราว</p>
                            </div>
                        </article>
                    </div>
                </section>

                <section class="row g-4">
                    <div class="col-12">
                        <article class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white d-flex align-items-center justify-content-between gap-3">
                                <h3 class="h5 mb-0">รายชื่อวิทยากร</h3>
                                <div class="d-flex flex-wrap gap-2">
                                    <a class="btn btn-outline-primary btn-sm" href="<?= site_url('admin/instructors'); ?>">รีเฟรช</a>
                                    <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#instructorFormModal">
                                        เพิ่มวิทยากร
                                    </button>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>วิทยากร</th>
                                                <th>สังกัด</th>
                                                <th>ติดต่อ</th>
                                                <th>ความเชี่ยวชาญ</th>
                                                <th>สถานะ</th>
                                                <th>จัดการ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($instructors)): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-secondary py-4">ยังไม่มีข้อมูลวิทยากร</td>
                                                </tr>
                                            <?php endif; ?>

                                            <?php foreach ($instructors as $instructor): ?>
                                                <?php
                                                $is_active = (int) $instructor->is_active === 1;
                                                $initial = function_exists('mb_substr') ? mb_substr($instructor->name, 0, 1, 'UTF-8') : substr($instructor->name, 0, 1);
                                                ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-3">
                                                            <?php if (!empty($instructor->photo)): ?>
                                                                <img class="rounded-circle object-fit-cover" src="<?= base_url($instructor->photo); ?>" alt="<?= html_escape($instructor->name); ?>" width="52" height="52">
                                                            <?php else: ?>
                                                                <span class="admin-avatar flex-shrink-0"><?= html_escape($initial); ?></span>
                                                            <?php endif; ?>
                                                            <div>
                                                                <strong class="d-block"><?= html_escape($instructor->name); ?></strong>
                                                                <span class="small text-secondary"><?= html_escape($instructor->position ?: '-'); ?></span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><?= html_escape($instructor->department ?: '-'); ?></td>
                                                    <td>
                                                        <?php if (!empty($instructor->email)): ?>
                                                            <a class="d-block" href="mailto:<?= html_escape($instructor->email); ?>"><?= html_escape($instructor->email); ?></a>
                                                        <?php else: ?>
                                                            <span class="d-block text-secondary">-</span>
                                                        <?php endif; ?>
                                                        <?php if (!empty($instructor->phone)): ?>
                                                            <span class="small text-secondary"><?= html_escape($instructor->phone); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-secondary"><?= nl2br(html_escape($instructor->bio ?: '-')); ?></td>
                                                    <td>
                                                        <span class="badge <?= $is_active ? 'text-bg-success' : 'text-bg-secondary'; ?>">
                                                            <?= $is_active ? 'ใช้งาน' : 'ปิดใช้งาน'; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/instructors?edit='.$instructor->id); ?>">แก้ไข</a>
                                                            <form class="js-instructor-delete-form" method="post" action="<?= site_url('admin/instructors/delete/'.$instructor->id); ?>" data-instructor-name="<?= html_escape($instructor->name); ?>">
                                                                <button class="btn btn-sm btn-outline-danger" type="submit">ลบ</button>
                                                            </form>
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
                </section>

                <div class="modal fade" id="instructorFormModal" tabindex="-1" aria-labelledby="instructorFormModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form method="post" action="<?= $form_action; ?>" enctype="multipart/form-data">
                                <div class="modal-header bg-primary text-white">
                                    <h2 class="modal-title h5" id="instructorFormModalLabel"><?= $is_edit ? 'แก้ไขวิทยากร' : 'เพิ่มวิทยากร'; ?></h2>
                                    <a class="btn-close" href="<?= site_url('admin/instructors'); ?>" aria-label="Close"></a>
                                </div>

                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-md-8">
                                            <label class="form-label fw-bold" for="name">ชื่อวิทยากร</label>
                                            <input class="form-control" id="name" type="text" name="name" value="<?= $is_edit ? html_escape($edit_instructor->name) : ''; ?>" required>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-bold" for="is_active">สถานะ</label>
                                            <select class="form-select" id="is_active" name="is_active">
                                                <option value="1" <?= (int) $edit_instructor->is_active === 1 ? 'selected' : ''; ?>>ใช้งาน</option>
                                                <option value="0" <?= (int) $edit_instructor->is_active === 0 ? 'selected' : ''; ?>>ปิดใช้งาน</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold" for="position">ตำแหน่ง</label>
                                            <input class="form-control" id="position" type="text" name="position" value="<?= $is_edit ? html_escape($edit_instructor->position) : ''; ?>">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold" for="department">หน่วยงาน/สังกัด</label>
                                            <input class="form-control" id="department" type="text" name="department" value="<?= $is_edit ? html_escape($edit_instructor->department) : ''; ?>">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold" for="email">อีเมล</label>
                                            <input class="form-control" id="email" type="email" name="email" value="<?= $is_edit ? html_escape($edit_instructor->email) : ''; ?>">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold" for="phone">เบอร์โทร</label>
                                            <input class="form-control" id="phone" type="text" name="phone" value="<?= $is_edit ? html_escape($edit_instructor->phone) : ''; ?>">
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-bold" for="photo">รูปภาพ</label>
                                            <input class="form-control" id="photo" type="file" name="photo" accept="image/jpeg,image/png,image/gif,image/webp">
                                            <?php if ($is_edit && !empty($edit_instructor->photo)): ?>
                                                <div class="d-flex align-items-center gap-3 mt-2">
                                                    <img class="rounded-circle object-fit-cover" src="<?= base_url($edit_instructor->photo); ?>" alt="<?= html_escape($edit_instructor->name); ?>" width="56" height="56">
                                                    <span class="small text-secondary">เลือกรูปใหม่เพื่อแทนที่รูปเดิม</span>
                                                </div>
                                            <?php endif; ?>
                                            <div class="form-text">รองรับ JPG, PNG, GIF, WEBP ขนาดไม่เกิน 4MB</div>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-bold" for="bio">ประวัติ/ความเชี่ยวชาญ</label>
                                            <textarea class="form-control" id="bio" name="bio" rows="4"><?= $is_edit ? html_escape($edit_instructor->bio) : ''; ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <a class="btn btn-outline-dark" href="<?= site_url('admin/instructors'); ?>">ยกเลิก</a>
                                    <button class="btn btn-primary" type="submit">
                                        <?= $is_edit ? 'บันทึกการแก้ไข' : 'เพิ่มวิทยากร'; ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <?php if ($is_edit): ?>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            var modal = new bootstrap.Modal(document.getElementById('instructorFormModal'));
                            modal.show();
                        });
                    </script>
                <?php endif; ?>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <script>
                    document.querySelectorAll('.js-instructor-delete-form').forEach(function (form) {
                        form.addEventListener('submit', function (event) {
                            event.preventDefault();

                            Swal.fire({
                                title: 'ยืนยันการลบวิทยากร?',
                                text: 'ต้องการลบ "' + form.dataset.instructorName + '" ใช่หรือไม่',
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
            </main>
<?php $this->load->view('admins/layouts/footer'); ?>
