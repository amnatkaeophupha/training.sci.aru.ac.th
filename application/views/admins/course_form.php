<?php
$this->load->view('admins/layouts/header');
$this->load->view('admins/layouts/sidebar');

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
$is_edit = !empty($edit_course->id);
$form_action = $is_edit
    ? site_url('admin/courses/update/'.$edit_course->id)
    : site_url('admin/courses/store');
$status_labels = array(
    1 => 'ร่าง',
    2 => 'เปิด',
    3 => 'ปิด'
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
$page_title = $is_edit ? 'แก้ไขหลักสูตร' : 'เพิ่มหลักสูตร';
?>
<div class="admin-content">
    <header class="admin-topbar d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 px-3 px-lg-4 py-3 border-bottom">
        <div><h1 class="h4 mb-1"><?= $page_title; ?></h1><p class="mb-0 text-secondary">กรอกข้อมูลหลักสูตรและกำหนดเงื่อนไขการสมัคร</p></div>
        <a class="btn btn-outline-dark" href="<?= site_url('admin/courses'); ?>">กลับรายการหลักสูตร</a>
    </header>
    <main class="admin-main py-4">
        <?php if (!empty($form_error)): ?>
            <div class="alert alert-danger" role="alert"><?= html_escape($form_error); ?></div>
        <?php endif; ?>
        <?php if (empty($categories)): ?>
            <div class="alert alert-warning" role="alert">กรุณาเพิ่มหมวดหมู่หลักสูตรก่อนเพิ่มข้อมูลหลักสูตร <a href="<?= site_url('admin/categories'); ?>">จัดการหมวดหมู่</a></div>
        <?php endif; ?>
        <form method="post" action="<?= $form_action; ?>" enctype="multipart/form-data">
            <section class="card border-0 shadow-sm mb-4"><div class="card-body p-3 p-lg-4"><h2 class="h5 mb-4">ข้อมูลทั่วไป</h2><div class="row g-3">
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
                                            <input class="form-control" id="title" type="text" name="title" value="<?= html_escape($edit_course->title); ?>" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold" for="slug">Slug</label>
                                            <input class="form-control" id="slug" type="text" name="slug" value="<?= html_escape($edit_course->slug); ?>" placeholder="เช่น python-data-analysis">
                                            <div class="form-text">เว้นว่างได้ ระบบจะสร้างจากชื่อหลักสูตรภาษาอังกฤษให้อัตโนมัติ</div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold" for="cover_image">รูปภาพปก 1140 x 420 px</label>
                                            <input class="form-control" id="cover_image" type="file" name="cover_image" accept="image/jpeg,image/png,image/gif,image/webp">
                                            <?php if (!empty($edit_course->cover_image)): ?>
                                                <div class="d-flex align-items-center gap-3 mt-2">
                                                    <img class="rounded object-fit-cover" src="<?= base_url($edit_course->cover_image); ?>" alt="<?= html_escape($edit_course->title); ?>" width="96" height="54">
                                                    <span class="small text-secondary">เลือกรูปใหม่เพื่อแทนที่รูปเดิม</span>
                                                </div>
                                            <?php endif; ?>
                                            <div class="form-text">แนะนำขนาด 1140 x 420 px รองรับ JPG, PNG, GIF, WEBP ไม่เกิน 4MB</div>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-bold" for="short_description">คำอธิบายสั้น</label>
                                            <textarea class="form-control" id="short_description" name="short_description" rows="3"><?= html_escape($edit_course->short_description); ?></textarea>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-bold" for="description">รายละเอียดหลักสูตร</label>
                                            <textarea class="form-control" id="description" name="description" rows="6"><?= html_escape($edit_course->description); ?></textarea>
                                        </div>

                                        </div></div></section>
<section class="card border-0 shadow-sm mb-4"><div class="card-body p-3 p-lg-4"><h2 class="h5 mb-4">การอบรม</h2><div class="row g-3">
<div class="col-12 col-md-6">
                                            <label class="form-label fw-bold" for="level">ระดับหลักสูตร</label>
                                            <input class="form-control" id="level" type="text" name="level" value="<?= html_escape($edit_course->level); ?>" placeholder="เริ่มต้น / กลาง / สูง">
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label class="form-label fw-bold" for="training_type">รูปแบบอบรม</label>
                                            <select class="form-select" id="training_type" name="training_type">
                                                <?php foreach ($type_labels as $value => $label): ?>
                                                    <option value="<?= html_escape($value); ?>" <?= $edit_course->training_type === $value ? 'selected' : ''; ?>>
                                                        <?= html_escape($label); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label class="form-label fw-bold" for="location">สถานที่อบรม</label>
                                            <input class="form-control" id="location" type="text" name="location" value="<?= html_escape($edit_course->location); ?>">
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label class="form-label fw-bold" for="duration_text">ระยะเวลา</label>
                                            <input class="form-control" id="duration_text" type="text" name="duration_text" value="<?= html_escape($edit_course->duration_text); ?>" placeholder="เช่น 2 วัน">
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label class="form-label fw-bold" for="capacity">จำนวนรับ</label>
                                            <input class="form-control" id="capacity" type="number" name="capacity" value="<?= (int) $edit_course->capacity; ?>" min="0">
                                        </div>

                                        </div></div></section>
<section class="card border-0 shadow-sm mb-4"><div class="card-body p-3 p-lg-4"><h2 class="h5 mb-4">ค่าสมัคร</h2><div class="row g-3">
<div class="col-12 col-md-6" id="fixed-fee-field">
                                            <label class="form-label fw-bold" for="fee">ค่าสมัคร</label>
                                            <input class="form-control" id="fee" type="number" name="fee" value="<?= html_escape((string) $edit_course->fee); ?>" min="0" step="0.01">
                                        </div>

                                        <?php include __DIR__.'/course_fee_fields.php'; ?>

                                        </div></div></section>
<section class="card border-0 shadow-sm mb-4"><div class="card-body p-3 p-lg-4"><h2 class="h5 mb-4">การเผยแพร่</h2><div class="row g-3">
<div class="col-12 col-md-6">
                                            <label class="form-label fw-bold" for="status">สถานะ</label>
                                            <select class="form-select" id="status" name="status">
                                                <?php foreach ($status_labels as $value => $label): ?>
                                                    <option value="<?= (int) $value; ?>" <?= (int) $edit_course->status === (int) $value ? 'selected' : ''; ?>>
                                                        <?= html_escape($label); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label class="form-label fw-bold" for="is_featured">แสดงหน้าแรก</label>
                                            <select class="form-select" id="is_featured" name="is_featured">
                                                <option value="0" <?= (int) $edit_course->is_featured === 0 ? 'selected' : ''; ?>>ไม่แสดง</option>
                                                <option value="1" <?= (int) $edit_course->is_featured === 1 ? 'selected' : ''; ?>>แสดงเป็นหลักสูตรเด่น</option>
                                            </select>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label class="form-label fw-bold" for="published_at">วันที่เผยแพร่</label>
                                            <input class="form-control" id="published_at" type="datetime-local" name="published_at" value="<?= html_escape($published_value); ?>">
                                        </div>

            </div></div></section>
            <div class="d-flex flex-wrap justify-content-end gap-2 mb-4">
                <a class="btn btn-outline-dark" href="<?= site_url('admin/courses'); ?>">ยกเลิก</a>
                <button class="btn btn-primary" type="submit" <?= empty($categories) ? 'disabled' : ''; ?>>บันทึก</button>
            </div>
        </form>
    </main>
<script src="<?= base_url('assets/js/course-fees.js'); ?>"></script>
<?php $this->load->view('admins/layouts/footer'); ?>
