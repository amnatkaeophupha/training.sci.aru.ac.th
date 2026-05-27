<?php
$this->load->view('admins/layouts/header');
$this->load->view('admins/layouts/sidebar');

$documents = isset($documents) ? $documents : array();
$courses = isset($courses) ? $courses : array();
$edit_document = isset($edit_document) && is_object($edit_document) ? $edit_document : (object) array(
    'id' => NULL,
    'course_id' => '',
    'title' => '',
    'file_path' => '',
    'file_type' => '',
    'file_size' => 0,
    'sort_order' => 0,
    'is_public' => 1
);
$stats = array_merge(
    array(
        'total' => 0,
        'public' => 0,
        'private' => 0
    ),
    isset($stats) && is_array($stats) ? $stats : array()
);

$is_edit = !empty($edit_document->id);
$form_action = $is_edit
    ? site_url('admin/documents/update/'.$edit_document->id)
    : site_url('admin/documents/store');
$format_size = function ($bytes) {
    $bytes = (int) $bytes;

    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2).' MB';
    }

    if ($bytes >= 1024) {
        return number_format($bytes / 1024, 2).' KB';
    }

    return number_format($bytes).' bytes';
};
?>
        <div class="admin-content">
            <header class="admin-topbar d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 px-3 px-lg-4 py-3 border-bottom">
                <div>
                    <h1 class="h4 mb-1">จัดการเอกสาร</h1>
                    <p class="mb-0 text-secondary">อัปโหลดและจัดการไฟล์เอกสารที่เชื่อมกับแต่ละหลักสูตร</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <?php if ($is_edit): ?>
                        <a class="btn btn-outline-dark" href="<?= site_url('admin/documents'); ?>">ยกเลิกแก้ไข</a>
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

                <?php if (empty($courses)): ?>
                    <div class="alert alert-warning fw-semibold" role="alert">
                        กรุณาเพิ่มหลักสูตรก่อนเพิ่มเอกสาร
                    </div>
                <?php endif; ?>

                <section class="row row-cols-1 row-cols-md-3 g-3 mb-4" aria-label="สรุปเอกสาร">
                    <div class="col">
                        <article class="admin-stat-card card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">เอกสารทั้งหมด</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['total']); ?></strong>
                                <p class="mb-0 text-secondary small">ไฟล์ในระบบ</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-green card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">เผยแพร่</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['public']); ?></strong>
                                <p class="mb-0 text-secondary small">ผู้ใช้ดูได้</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-red card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">ส่วนตัว</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['private']); ?></strong>
                                <p class="mb-0 text-secondary small">เฉพาะผู้ดูแล</p>
                            </div>
                        </article>
                    </div>
                </section>

                <section class="row g-4">
                    <div class="col-12">
                        <article class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white d-flex align-items-center justify-content-between gap-3">
                                <h3 class="h5 mb-0">รายการเอกสาร</h3>
                                <div class="d-flex flex-wrap gap-2">
                                    <a class="btn btn-outline-primary btn-sm" href="<?= site_url('admin/documents'); ?>">รีเฟรช</a>
                                    <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#documentFormModal" <?= empty($courses) ? 'disabled' : ''; ?>>
                                        เพิ่มเอกสาร
                                    </button>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>เอกสาร</th>
                                                <th>หลักสูตร</th>
                                                <th>ไฟล์</th>
                                                <th>ลำดับ</th>
                                                <th>สถานะ</th>
                                                <th>จัดการ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($documents)): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-secondary py-4">ยังไม่มีข้อมูลเอกสาร</td>
                                                </tr>
                                            <?php endif; ?>

                                            <?php foreach ($documents as $document): ?>
                                                <?php $is_public = (int) $document->is_public === 1; ?>
                                                <tr>
                                                    <td>
                                                        <strong class="d-block"><?= html_escape($document->title); ?></strong>
                                                        <span class="small text-secondary"><?= html_escape(strtoupper($document->file_type ?: '-')); ?></span>
                                                    </td>
                                                    <td>
                                                        <strong class="d-block"><?= html_escape($document->course_title ?: '-'); ?></strong>
                                                        <code class="small"><?= html_escape($document->course_slug ?: ''); ?></code>
                                                    </td>
                                                    <td>
                                                        <a class="btn btn-sm btn-outline-dark" href="<?= base_url($document->file_path); ?>" target="_blank" rel="noopener">เปิดไฟล์</a>
                                                        <span class="small text-secondary ms-2"><?= html_escape($format_size($document->file_size)); ?></span>
                                                    </td>
                                                    <td><?= number_format((int) $document->sort_order); ?></td>
                                                    <td>
                                                        <span class="badge <?= $is_public ? 'text-bg-success' : 'text-bg-secondary'; ?>">
                                                            <?= $is_public ? 'เผยแพร่' : 'ส่วนตัว'; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/documents?edit='.$document->id); ?>">แก้ไข</a>
                                                            <form class="js-document-delete-form" method="post" action="<?= site_url('admin/documents/delete/'.$document->id); ?>" data-document-title="<?= html_escape($document->title); ?>">
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

                <div class="modal fade" id="documentFormModal" tabindex="-1" aria-labelledby="documentFormModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form method="post" action="<?= $form_action; ?>" enctype="multipart/form-data">
                                <div class="modal-header bg-primary text-white">
                                    <h2 class="modal-title h5" id="documentFormModalLabel"><?= $is_edit ? 'แก้ไขเอกสาร' : 'เพิ่มเอกสาร'; ?></h2>
                                    <a class="btn-close" href="<?= site_url('admin/documents'); ?>" aria-label="Close"></a>
                                </div>

                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-md-8">
                                            <label class="form-label fw-bold" for="course_id">หลักสูตร</label>
                                            <select class="form-select" id="course_id" name="course_id" required>
                                                <option value="">เลือกหลักสูตร</option>
                                                <?php foreach ($courses as $course): ?>
                                                    <option value="<?= (int) $course->id; ?>" <?= (int) $edit_document->course_id === (int) $course->id ? 'selected' : ''; ?>>
                                                        <?= html_escape($course->title); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-bold" for="sort_order">ลำดับ</label>
                                            <input class="form-control" id="sort_order" type="number" name="sort_order" value="<?= (int) $edit_document->sort_order; ?>">
                                        </div>

                                        <div class="col-md-8">
                                            <label class="form-label fw-bold" for="title">ชื่อเอกสาร</label>
                                            <input class="form-control" id="title" type="text" name="title" value="<?= $is_edit ? html_escape($edit_document->title) : ''; ?>" required>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-bold" for="is_public">สถานะ</label>
                                            <select class="form-select" id="is_public" name="is_public">
                                                <option value="1" <?= (int) $edit_document->is_public === 1 ? 'selected' : ''; ?>>เผยแพร่</option>
                                                <option value="0" <?= (int) $edit_document->is_public === 0 ? 'selected' : ''; ?>>ส่วนตัว</option>
                                            </select>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-bold" for="document_file">ไฟล์เอกสาร</label>
                                            <input class="form-control" id="document_file" type="file" name="document_file" <?= $is_edit ? '' : 'required'; ?>>
                                            <?php if ($is_edit && !empty($edit_document->file_path)): ?>
                                                <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                                                    <a class="btn btn-sm btn-outline-dark" href="<?= base_url($edit_document->file_path); ?>" target="_blank" rel="noopener">เปิดไฟล์เดิม</a>
                                                    <span class="small text-secondary">เลือกไฟล์ใหม่เพื่อแทนที่ไฟล์เดิม</span>
                                                </div>
                                            <?php endif; ?>
                                            <div class="form-text">รองรับ PDF, Word, Excel, PowerPoint, รูปภาพ, ZIP/RAR ขนาดไม่เกิน 20MB</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <a class="btn btn-outline-dark" href="<?= site_url('admin/documents'); ?>">ยกเลิก</a>
                                    <button class="btn btn-primary" type="submit" <?= empty($courses) ? 'disabled' : ''; ?>>
                                        <?= $is_edit ? 'บันทึกการแก้ไข' : 'เพิ่มเอกสาร'; ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <?php if ($is_edit): ?>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            var modal = new bootstrap.Modal(document.getElementById('documentFormModal'));
                            modal.show();
                        });
                    </script>
                <?php endif; ?>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <script>
                    document.querySelectorAll('.js-document-delete-form').forEach(function (form) {
                        form.addEventListener('submit', function (event) {
                            event.preventDefault();

                            Swal.fire({
                                title: 'ยืนยันการลบเอกสาร?',
                                text: 'ต้องการลบ "' + form.dataset.documentTitle + '" ใช่หรือไม่',
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
