<?php
$this->load->view('admins/layouts/header');
$this->load->view('admins/layouts/sidebar');

$courses = isset($courses) ? $courses : array();
$categories = isset($categories) ? $categories : array();
$edit_course = isset($edit_course) && is_object($edit_course) ? $edit_course : (object) array(
    'id' => NULL,
    'category_id' => '',
    'title' => '',
    'slug' => '',
    'short_description' => '',
    'description' => '',
    'cover_image' => '',
    'level' => '',
    'training_type' => '',
    'location' => '',
    'duration_text' => '',
    'capacity' => 0,
    'fee' => 0,
    'status' => 1,
    'is_featured' => 0,
    'published_at' => ''
);
$stats = array_merge(
    array(
        'total' => 0,
        'draft' => 0,
        'open' => 0,
        'closed' => 0,
        'featured' => 0
    ),
    isset($stats) && is_array($stats) ? $stats : array()
);

$is_edit = !empty($edit_course->id);
$form_action = $is_edit
    ? site_url('admin/courses/update/'.$edit_course->id)
    : site_url('admin/courses/store');
$status_labels = array(
    1 => 'ร่าง',
    2 => 'เปิด',
    3 => 'ปิด'
);
$status_classes = array(
    1 => 'text-bg-secondary',
    2 => 'text-bg-success',
    3 => 'text-bg-danger'
);
$type_labels = array(
    '' => 'ไม่ระบุ',
    'online' => 'Online',
    'onsite' => 'Onsite',
    'hybrid' => 'Hybrid'
);
$published_value = '';

if (!empty($edit_course->published_at)) {
    $published_time = strtotime($edit_course->published_at);
    $published_value = $published_time ? date('Y-m-d\TH:i', $published_time) : '';
}
?>
        <div class="admin-content">
            <header class="admin-topbar d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 px-3 px-lg-4 py-3 border-bottom">
                <div>
                    <h1 class="h4 mb-1">หลักสูตร</h1>
                    <p class="mb-0 text-secondary">จัดการข้อมูลหลักสูตรอบรมและเชื่อมโยงกับหมวดหมู่หลักสูตร</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <?php if ($is_edit): ?>
                        <a class="btn btn-outline-dark" href="<?= site_url('admin/courses'); ?>">ยกเลิกแก้ไข</a>
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

                <?php if (empty($categories)): ?>
                    <div class="alert alert-warning fw-semibold" role="alert">
                        กรุณาเพิ่มหมวดหมู่หลักสูตรก่อนเพิ่มข้อมูลหลักสูตร
                    </div>
                <?php endif; ?>

                <section class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3 mb-4" aria-label="สรุปหลักสูตร">
                    <div class="col">
                        <article class="admin-stat-card card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">หลักสูตรทั้งหมด</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['total']); ?></strong>
                                <p class="mb-0 text-secondary small">รายการในระบบ</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-green card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">เปิดใช้งาน</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['open']); ?></strong>
                                <p class="mb-0 text-secondary small">พร้อมแสดงผล</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-blue card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">ร่าง</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['draft']); ?></strong>
                                <p class="mb-0 text-secondary small">ยังไม่เผยแพร่</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-red card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">แนะนำหน้าแรก</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['featured']); ?></strong>
                                <p class="mb-0 text-secondary small">หลักสูตรเด่น</p>
                            </div>
                        </article>
                    </div>
                </section>

                <section class="row g-4">

                    <div class="col-12">
                        <article class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between gap-3">
                                <h3 class="h5 mb-0">รายการหลักสูตร</h3>
                                <div class="d-flex flex-wrap gap-2">
                                    <a class="btn btn-light btn-sm text-primary" href="<?= site_url('admin/categories'); ?>">หมวดหมู่หลักสูตร</a>
                                    <a class="btn btn-light btn-sm text-primary" href="<?= site_url('admin/courses'); ?>">รีเฟรช</a>
                                    <?php if ($is_edit): ?>
                                        <a class="btn btn-warning btn-sm text-dark" href="<?= site_url('admin/courses'); ?>">เพิ่มหลักสูตร</a>
                                    <?php else: ?>
                                        <button class="btn btn-warning btn-sm text-dark" type="button" data-bs-toggle="modal" data-bs-target="#courseFormModal" <?= empty($categories) ? 'disabled' : ''; ?>>
                                            เพิ่มหลักสูตร
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>หลักสูตร</th>
                                                <th>หมวดหมู่</th>
                                                <th>รูปแบบ</th>
                                                <th>รับ</th>
                                                <th>ค่าสมัคร</th>
                                                <th>สถานะ</th>
                                                <th>จัดการ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($courses)): ?>
                                                <tr>
                                                    <td colspan="7" class="text-center text-secondary py-4">ยังไม่มีข้อมูลหลักสูตร</td>
                                                </tr>
                                            <?php endif; ?>

                                            <?php foreach ($courses as $course): ?>
                                                <?php $course_status = (int) $course->status; ?>
                                                <tr>
                                                    <td>
                                                        <strong class="d-block"><?= html_escape($course->title); ?></strong>
                                                        <code class="small"><?= html_escape($course->slug); ?></code>
                                                        <?php if ((int) $course->is_featured === 1): ?>
                                                            <span class="badge text-bg-warning ms-1">แนะนำ</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= html_escape($course->category_name ?: '-'); ?></td>
                                                    <td><?= html_escape(isset($type_labels[$course->training_type]) ? $type_labels[$course->training_type] : $course->training_type); ?></td>
                                                    <td><?= number_format((int) $course->capacity); ?></td>
                                                    <td><?= number_format((float) $course->fee, 2); ?></td>
                                                    <td>
                                                        <span class="badge <?= isset($status_classes[$course_status]) ? $status_classes[$course_status] : 'text-bg-secondary'; ?>">
                                                            <?= html_escape(isset($status_labels[$course_status]) ? $status_labels[$course_status] : $course->status); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/courses?edit='.$course->id); ?>">แก้ไข</a>
                                                            <a class="btn btn-sm btn-outline-info" href="<?= site_url('admin/course-details/'.$course->id); ?>">รายละเอียด</a>
                                                            <a class="btn btn-sm btn-outline-success" href="<?= site_url('admin/course-instructors/'.$course->id); ?>">วิทยากร</a>
                                                            <?php if ((int) $course->batch_count > 0): ?>
                                                                <button class="btn btn-sm btn-outline-danger" type="button" disabled title="มีรุ่นอบรม ไม่สามารถลบได้">ลบ</button>
                                                                <small class="text-secondary align-self-center">มีรุ่นอบรม <?= number_format((int) $course->batch_count); ?> รุ่น</small>
                                                            <?php else: ?>
                                                                <form class="js-course-delete-form" method="post" action="<?= site_url('admin/courses/delete/'.$course->id); ?>" data-course-title="<?= html_escape($course->title); ?>">
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

                </section>

                <div class="modal fade" id="courseFormModal" tabindex="-1" aria-labelledby="courseFormModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <form class="d-flex flex-column" method="post" action="<?= $form_action; ?>" enctype="multipart/form-data">
                                <div class="modal-header bg-primary text-white">
                                    <h2 class="modal-title h5" id="courseFormModalLabel"><?= $is_edit ? 'แก้ไขหลักสูตร' : 'เพิ่มหลักสูตร'; ?></h2>
                                    <a class="btn-close" href="<?= site_url('admin/courses'); ?>" aria-label="Close"></a>
                                </div>

                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold" for="category_id">หมวดหมู่</label>
                                            <select class="form-select" id="category_id" name="category_id" required>
                                                <option value="">เลือกหมวดหมู่</option>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?= (int) $category->id; ?>" <?= (int) $edit_course->category_id === (int) $category->id ? 'selected' : ''; ?>>
                                                        <?= html_escape($category->name); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold" for="title">ชื่อหลักสูตร</label>
                                            <input class="form-control" id="title" type="text" name="title" value="<?= $is_edit ? html_escape($edit_course->title) : ''; ?>" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold" for="slug">Slug</label>
                                            <input class="form-control" id="slug" type="text" name="slug" value="<?= $is_edit ? html_escape($edit_course->slug) : ''; ?>" placeholder="เช่น python-data-analysis">
                                            <div class="form-text">เว้นว่างได้ ระบบจะสร้างจากชื่อหลักสูตรภาษาอังกฤษให้อัตโนมัติ</div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold" for="cover_image">รูปภาพปก 1140 x 420 px</label>
                                            <input class="form-control" id="cover_image" type="file" name="cover_image" accept="image/jpeg,image/png,image/gif,image/webp">
                                            <?php if ($is_edit && !empty($edit_course->cover_image)): ?>
                                                <div class="d-flex align-items-center gap-3 mt-2">
                                                    <img class="rounded object-fit-cover" src="<?= base_url($edit_course->cover_image); ?>" alt="<?= html_escape($edit_course->title); ?>" width="96" height="54">
                                                    <span class="small text-secondary">เลือกรูปใหม่เพื่อแทนที่รูปเดิม</span>
                                                </div>
                                            <?php endif; ?>
                                            <div class="form-text">แนะนำขนาด 1140 x 420 px รองรับ JPG, PNG, GIF, WEBP ไม่เกิน 4MB</div>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-bold" for="short_description">คำอธิบายสั้น</label>
                                            <textarea class="form-control" id="short_description" name="short_description" rows="1"><?= $is_edit ? html_escape($edit_course->short_description) : ''; ?></textarea>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-bold" for="description">รายละเอียดหลักสูตร</label>
                                            <textarea class="form-control" id="description" name="description" rows="3"><?= $is_edit ? html_escape($edit_course->description) : ''; ?></textarea>
                                        </div>

                                        <div class="col-12 col-md-6 col-xl-3">
                                            <label class="form-label fw-bold" for="level">ระดับหลักสูตร</label>
                                            <input class="form-control" id="level" type="text" name="level" value="<?= $is_edit ? html_escape($edit_course->level) : ''; ?>" placeholder="เริ่มต้น / กลาง / สูง">
                                        </div>

                                        <div class="col-12 col-md-6 col-xl-3">
                                            <label class="form-label fw-bold" for="training_type">รูปแบบอบรม</label>
                                            <select class="form-select" id="training_type" name="training_type">
                                                <?php foreach ($type_labels as $value => $label): ?>
                                                    <option value="<?= html_escape($value); ?>" <?= $edit_course->training_type === $value ? 'selected' : ''; ?>>
                                                        <?= html_escape($label); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-12 col-md-6 col-xl-6">
                                            <label class="form-label fw-bold" for="location">สถานที่อบรม</label>
                                            <input class="form-control" id="location" type="text" name="location" value="<?= $is_edit ? html_escape($edit_course->location) : ''; ?>">
                                        </div>

                                        <div class="col-12 col-md-6 col-xl-3">
                                            <label class="form-label fw-bold" for="duration_text">ระยะเวลา</label>
                                            <input class="form-control" id="duration_text" type="text" name="duration_text" value="<?= $is_edit ? html_escape($edit_course->duration_text) : ''; ?>" placeholder="เช่น 2 วัน">
                                        </div>

                                        <div class="col-12 col-md-6 col-xl-3">
                                            <label class="form-label fw-bold" for="capacity">จำนวนรับ</label>
                                            <input class="form-control" id="capacity" type="number" name="capacity" value="<?= (int) $edit_course->capacity; ?>" min="0">
                                        </div>

                                        <div class="col-12 col-md-6 col-xl-3">
                                            <label class="form-label fw-bold" for="fee">ค่าสมัคร</label>
                                            <input class="form-control" id="fee" type="number" name="fee" value="<?= html_escape((string) $edit_course->fee); ?>" min="0" step="0.01">
                                        </div>

                                        <div class="col-12 col-md-6 col-xl-3">
                                            <label class="form-label fw-bold" for="status">สถานะ</label>
                                            <select class="form-select" id="status" name="status">
                                                <?php foreach ($status_labels as $value => $label): ?>
                                                    <option value="<?= (int) $value; ?>" <?= (int) $edit_course->status === (int) $value ? 'selected' : ''; ?>>
                                                        <?= html_escape($label); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-12 col-md-6 col-xl-3">
                                            <label class="form-label fw-bold" for="is_featured">แสดงหน้าแรก</label>
                                            <select class="form-select" id="is_featured" name="is_featured">
                                                <option value="0" <?= (int) $edit_course->is_featured === 0 ? 'selected' : ''; ?>>ไม่แสดง</option>
                                                <option value="1" <?= (int) $edit_course->is_featured === 1 ? 'selected' : ''; ?>>แสดงเป็นหลักสูตรเด่น</option>
                                            </select>
                                        </div>

                                        <div class="col-12 col-md-6 col-xl-3">
                                            <label class="form-label fw-bold" for="published_at">วันที่เผยแพร่</label>
                                            <input class="form-control" id="published_at" type="datetime-local" name="published_at" value="<?= html_escape($published_value); ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <a class="btn btn-outline-dark" href="<?= site_url('admin/courses'); ?>">ยกเลิก</a>
                                    <button class="btn btn-primary" type="submit" <?= empty($categories) ? 'disabled' : ''; ?>>
                                        บันทึก
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <?php if ($is_edit): ?>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            var modal = new bootstrap.Modal(document.getElementById('courseFormModal'));
                            modal.show();
                        });
                    </script>
                <?php endif; ?>
            </main>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.querySelectorAll('.js-course-delete-form').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();

                    Swal.fire({
                        title: 'ยืนยันการลบหลักสูตร?',
                        text: 'ต้องการลบ "' + form.dataset.courseTitle + '" ใช่หรือไม่',
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
