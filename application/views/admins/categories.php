<?php
$this->load->view('admins/layouts/header');
$this->load->view('admins/layouts/sidebar');

$categories = isset($categories) ? $categories : array();
$edit_category = isset($edit_category) && is_object($edit_category) ? $edit_category : (object) array(
    'id' => NULL,
    'name' => '',
    'slug' => '',
    'description' => '',
    'sort_order' => 0,
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

$is_edit = !empty($edit_category->id);
$form_action = $is_edit
    ? site_url('admin/categories/update/'.$edit_category->id)
    : site_url('admin/categories/store');
?>
        <div class="admin-content">
            <header class="admin-topbar d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 px-3 px-lg-4 py-3 border-bottom">
                <div>
                    <h1 class="h4 mb-1">หมวดหมู่หลักสูตร</h1>
                    <p class="mb-0 text-secondary">จัดการข้อมูลหมวดหมู่สำหรับหลักสูตรอบรม</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <?php if ($is_edit): ?>
                        <a class="btn btn-outline-dark" href="<?= site_url('admin/categories'); ?>">ยกเลิกแก้ไข</a>
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

                <section class="row row-cols-1 row-cols-md-3 g-3 mb-4" aria-label="สรุปหมวดหมู่หลักสูตร">
                    <div class="col">
                        <article class="admin-stat-card card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">หมวดหมู่ทั้งหมด</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['total']); ?></strong>
                                <p class="mb-0 text-secondary small">รายการในระบบ</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-green card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">เปิดใช้งาน</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['active']); ?></strong>
                                <p class="mb-0 text-secondary small">แสดงในระบบหลักสูตร</p>
                            </div>
                        </article>
                    </div>
                    <div class="col">
                        <article class="admin-stat-card stat-red card h-100 border-0 shadow-sm position-relative">
                            <div class="card-body">
                                <span class="text-secondary small fw-bold">ปิดใช้งาน</span>
                                <strong class="d-block fs-2 lh-1 my-2"><?= number_format((int) $stats['inactive']); ?></strong>
                                <p class="mb-0 text-secondary small">ซ่อนชั่วคราว</p>
                            </div>
                        </article>
                    </div>
                </section>

                <section class="row g-4">
                    <div class="col-xl-8">
                        <article class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white d-flex align-items-center justify-content-between gap-3">
                                <h3 class="h5 mb-0">รายการหมวดหมู่หลักสูตร</h3>
                                <a class="fw-bold" href="<?= site_url('admin/categories'); ?>">รีเฟรช</a>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>ลำดับ</th>
                                                <th>ชื่อหมวดหมู่</th>
                                                <th>Slug</th>
                                                <th>สถานะ</th>
                                                <th>จัดการ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($categories)): ?>
                                                <tr>
                                                    <td colspan="5" class="text-center text-secondary py-4">ยังไม่มีข้อมูลหมวดหมู่หลักสูตร</td>
                                                </tr>
                                            <?php endif; ?>

                                            <?php foreach ($categories as $category): ?>
                                                <tr>
                                                    <td><?= number_format((int) $category->sort_order); ?></td>
                                                    <td>
                                                        <strong class="d-block"><?= html_escape($category->name); ?></strong>
                                                        <?php if (!empty($category->description)): ?>
                                                            <span class="small text-secondary"><?= html_escape($category->description); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><code><?= html_escape($category->slug); ?></code></td>
                                                    <td>
                                                        <span class="badge <?= (int) $category->is_active === 1 ? 'text-bg-success' : 'text-bg-secondary'; ?>">
                                                            <?= (int) $category->is_active === 1 ? 'เปิดใช้งาน' : 'ปิดใช้งาน'; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/categories?edit='.$category->id); ?>">แก้ไข</a>
                                                            <form method="post" action="<?= site_url('admin/categories/delete/'.$category->id); ?>" onsubmit="return confirm('ยืนยันการลบหมวดหมู่หลักสูตรนี้?');">
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

                    <div class="col-xl-4">
                        <aside class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h3 class="h5 mb-0"><?= $is_edit ? 'แก้ไขหมวดหมู่' : 'เพิ่มหมวดหมู่'; ?></h3>
                            </div>

                            <div class="card-body">
                                <form class="row g-3" method="post" action="<?= $form_action; ?>">
                                    <div class="col-12">
                                        <label class="form-label fw-bold" for="name">ชื่อหมวดหมู่</label>
                                        <input class="form-control" id="name" type="text" name="name" value="<?= $is_edit ? html_escape($edit_category->name) : ''; ?>" required>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-bold" for="slug">Slug</label>
                                        <input class="form-control" id="slug" type="text" name="slug" value="<?= $is_edit ? html_escape($edit_category->slug) : ''; ?>" placeholder="เช่น data-science">
                                        <div class="form-text">เว้นว่างได้ ระบบจะสร้างจากชื่อหมวดหมู่ภาษาอังกฤษให้อัตโนมัติ</div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-bold" for="description">รายละเอียด</label>
                                        <textarea class="form-control" id="description" name="description" rows="4"><?= $is_edit ? html_escape($edit_category->description) : ''; ?></textarea>
                                    </div>

                                    <div class="col-md-6 col-xl-12">
                                        <label class="form-label fw-bold" for="sort_order">ลำดับแสดงผล</label>
                                        <input class="form-control" id="sort_order" type="number" name="sort_order" value="<?= $is_edit ? (int) $edit_category->sort_order : 0; ?>" min="0">
                                    </div>

                                    <div class="col-md-6 col-xl-12">
                                        <label class="form-label fw-bold" for="is_active">สถานะ</label>
                                        <select class="form-select" id="is_active" name="is_active">
                                            <option value="1" <?= (int) $edit_category->is_active === 1 ? 'selected' : ''; ?>>เปิดใช้งาน</option>
                                            <option value="0" <?= (int) $edit_category->is_active === 0 ? 'selected' : ''; ?>>ปิดใช้งาน</option>
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <button class="btn btn-primary w-100" type="submit"><?= $is_edit ? 'บันทึกการแก้ไข' : 'เพิ่มหมวดหมู่'; ?></button>
                                    </div>
                                </form>
                            </div>
                        </aside>
                    </div>
                </section>
            </main>
<?php $this->load->view('admins/layouts/footer'); ?>
