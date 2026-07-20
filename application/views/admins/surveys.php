<?php
$this->load->view('admins/layouts/header');
$this->load->view('admins/layouts/sidebar');
$edit = isset($edit_survey) && $edit_survey ? $edit_survey : (object) array(
    'id' => 0,
    'title' => '',
    'description' => '',
    'status' => 1
);
?>
<div class="admin-content">
    <header class="admin-topbar d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 px-3 px-lg-4 py-3 border-bottom">
        <div>
            <h1 class="h4 mb-1">แบบประเมินหลังอบรม</h1>
            <p class="mb-0 text-secondary">สร้างชุดคำถามและนำกลับไปใช้กับหลักสูตรหรือรุ่นอบรมต่าง ๆ</p>
        </div>
        <?php if ($tables_ready && !$edit->id): ?>
            <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#surveyFormModal">
                สร้างแบบประเมิน
            </button>
        <?php elseif ($tables_ready): ?>
            <a class="btn btn-primary" href="<?= site_url('admin/surveys?create=1'); ?>">สร้างแบบประเมินใหม่</a>
        <?php endif; ?>
    </header>

    <main class="admin-main py-4">
        <?php foreach (array('success' => 'success', 'error' => 'danger') as $key => $class): ?>
            <?php if ($this->session->flashdata($key)): ?>
                <div class="alert alert-<?= $class; ?>"><?= html_escape($this->session->flashdata($key)); ?></div>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if (!$tables_ready): ?>
            <div class="alert alert-warning">
                ยังไม่พบตารางแบบประเมิน กรุณารันไฟล์ <code>Design/training_surveys.sql</code> ก่อนใช้งาน
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                        <h2 class="h5 mb-0">ชุดแบบประเมินทั้งหมด</h2>
                        <span class="badge text-bg-secondary"><?= number_format(count($surveys)); ?> ชุด</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>ชื่อ</th><th>คำถาม</th><th>เปิดใช้</th><th class="text-end">จัดการ</th></tr></thead>
                            <tbody>
                                <?php foreach ($surveys as $survey): ?>
                                    <tr>
                                        <td>
                                            <strong><?= html_escape($survey->title); ?></strong>
                                            <small class="d-block text-secondary"><?= html_escape($survey->description); ?></small>
                                        </td>
                                        <td><?= number_format($survey->question_count); ?></td>
                                        <td><?= number_format($survey->assignment_count); ?></td>
                                        <td class="text-nowrap text-end">
                                            <a class="btn btn-sm btn-outline-secondary" href="<?= site_url('admin/surveys?edit='.$survey->id); ?>">แก้ไข</a>
                                            <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/surveys/questions/'.$survey->id); ?>">คำถาม</a>
                                            <a class="btn btn-sm btn-success" href="<?= site_url('admin/surveys/assign/'.$survey->id); ?>">เปิดใช้</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (!$surveys): ?>
                                    <tr><td colspan="4" class="text-center text-secondary py-5">ยังไม่มีแบบประเมิน กด “สร้างแบบประเมิน” เพื่อเริ่มต้น</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php if ($assignments): ?>
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body">
                        <h2 class="h5">รายการที่เปิดใช้ล่าสุด</h2>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead><tr><th>แบบประเมิน</th><th>หลักสูตร/รุ่น</th><th>ช่วงเวลา</th><th>ตอบแล้ว</th><th></th></tr></thead>
                                <tbody>
                                    <?php foreach ($assignments as $assignment): ?>
                                        <tr>
                                            <td><?= html_escape($assignment->survey_title); ?></td>
                                            <td><?= html_escape($assignment->course_title); ?> / <?= html_escape($assignment->batch_no); ?></td>
                                            <td><?= html_escape($assignment->open_at); ?> – <?= html_escape($assignment->close_at); ?></td>
                                            <td><?= number_format($assignment->completed_count); ?> / <?= number_format($assignment->eligible_count); ?></td>
                                            <td><a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/surveys/assignment/'.$assignment->id); ?>">จัดการ</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="modal fade" id="surveyFormModal" tabindex="-1" aria-labelledby="surveyFormModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header">
                            <h2 class="modal-title fs-5" id="surveyFormModalLabel"><?= $edit->id ? 'แก้ไขแบบประเมิน' : 'สร้างแบบประเมิน'; ?></h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                        </div>
                        <form method="post" action="<?= site_url('admin/surveys/save/'.(int) $edit->id); ?>">
                            <div class="modal-body">
                                <label class="form-label fw-semibold" for="survey_title">ชื่อแบบประเมิน <span class="text-danger">*</span></label>
                                <input class="form-control" id="survey_title" name="title" required maxlength="255" value="<?= html_escape($edit->title); ?>">

                                <label class="form-label fw-semibold mt-3" for="survey_description">คำอธิบาย</label>
                                <textarea class="form-control" id="survey_description" name="description" rows="4"><?= html_escape($edit->description); ?></textarea>

                                <label class="form-label fw-semibold mt-3" for="survey_status">สถานะ</label>
                                <select class="form-select" id="survey_status" name="status">
                                    <option value="1" <?= (int) $edit->status === 1 ? 'selected' : ''; ?>>ฉบับร่าง</option>
                                    <option value="2" <?= (int) $edit->status === 2 ? 'selected' : ''; ?>>ใช้งาน</option>
                                    <option value="3" <?= (int) $edit->status === 3 ? 'selected' : ''; ?>>เก็บถาวร</option>
                                </select>
                            </div>
                            <div class="modal-footer">
                                <?php if ($edit->id): ?>
                                    <a class="btn btn-outline-secondary me-auto" href="<?= site_url('admin/surveys'); ?>">ยกเลิกการแก้ไข</a>
                                <?php else: ?>
                                    <button type="button" class="btn btn-outline-secondary me-auto" data-bs-dismiss="modal">ยกเลิก</button>
                                <?php endif; ?>
                                <button class="btn btn-primary" type="submit">บันทึกแบบประเมิน</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php if ($edit->id || $this->input->get('create', TRUE)): ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        var modalElement = document.getElementById('surveyFormModal');
                        if (modalElement && window.bootstrap) {
                            bootstrap.Modal.getOrCreateInstance(modalElement).show();
                        }
                    });
                </script>
            <?php endif; ?>
        <?php endif; ?>
    </main>
<?php $this->load->view('admins/layouts/footer'); ?>
