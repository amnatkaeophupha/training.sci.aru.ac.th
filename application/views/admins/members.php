<?php $this->load->view('admins/layouts/header'); $this->load->view('admins/layouts/sidebar'); ?>
<style>
.member-modal-header{background:linear-gradient(135deg,#173b67 0%,#2563a6 55%,#2f80c9 100%);color:#fff;border-bottom:0;padding:1.1rem 1.5rem;box-shadow:0 4px 16px rgba(23,59,103,.18)}
.member-modal-header .modal-title{font-weight:700;letter-spacing:.01em}
</style>
<div class="admin-content">
    <header class="admin-topbar d-flex justify-content-between align-items-center gap-3 px-3 px-lg-4 py-3 border-bottom">
        <div><h1 class="h4 mb-1">บริหารจัดการข้อมูลสมาชิก</h1>
        <p class="mb-0 text-secondary">ตรวจสอบข้อมูลและค้นหาสมาชิกที่ลงทะเบียนในระบบ</p></div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#memberModal" id="add-member">+ เพิ่มสมาชิก</button>
    </header>
    <main class="admin-main py-4">
        <?php if($this->session->flashdata('success')): ?><div class="alert alert-success"><?= html_escape($this->session->flashdata('success')) ?></div><?php endif; ?>
        <?php if($this->session->flashdata('error')): ?><div class="alert alert-danger"><?= html_escape($this->session->flashdata('error')) ?></div><?php endif; ?>
        <section class="row row-cols-1 row-cols-md-3 g-3 mb-4">
            <?php foreach (array('total'=>'สมาชิกทั้งหมด','active'=>'ใช้งาน','inactive'=>'ปิดใช้งาน') as $key=>$label): ?>
                <div class="col"><div class="card border-0 shadow-sm h-100"><div class="card-body"><span class="text-secondary small fw-bold"><?= $label ?></span><strong class="d-block fs-2 mt-2"><?= number_format($stats[$key]) ?></strong></div></div></div>
            <?php endforeach; ?>
        </section>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form class="row g-2 mb-4" method="get">
                    <div class="col-md-9"><input class="form-control" name="q" value="<?= html_escape($query) ?>" placeholder="ค้นหาชื่อ อีเมล โทรศัพท์ หรือหน่วยงาน"></div>
                    <div class="col-md-3 d-grid"><button class="btn btn-primary">ค้นหา</button></div>
                </form>
                <div class="table-responsive"><table class="table table-hover align-middle">
                    <thead><tr><th>ชื่อสมาชิก</th><th>ข้อมูลติดต่อ</th><th>ตำแหน่ง / หน่วยงาน</th><th>สถานะ</th><th>วันที่สมัคร</th><th>จัดการ</th></tr></thead>
                    <tbody>
                    <?php if (!$members): ?><tr><td colspan="6" class="text-center text-secondary py-4">ไม่พบข้อมูลสมาชิก</td></tr><?php endif; ?>
                    <?php foreach ($members as $member): ?><tr>
                        <td><strong><?= html_escape(trim($member->title_name.$member->first_name.' '.$member->last_name)) ?></strong></td>
                        <td><?= html_escape($member->email) ?><br><span class="small text-secondary"><?= html_escape($member->phone ?: '-') ?></span></td>
                        <td><?= html_escape($member->position_name ?: '-') ?><br><span class="small text-secondary"><?= html_escape($member->organization_name ?: '-') ?></span></td>
                        <td><span class="badge <?= (int)$member->status===1?'text-bg-success':'text-bg-secondary' ?>"><?= (int)$member->status===1?'ใช้งาน':'ปิดใช้งาน' ?></span></td>
                        <td><?= html_escape($member->created_at ?: '-') ?></td>
                        <td><div class="d-flex gap-2"><button type="button" class="btn btn-sm btn-outline-primary js-edit-member" data-bs-toggle="modal" data-bs-target="#memberModal" data-id="<?= (int)$member->id ?>" data-title-name="<?= html_escape($member->title_name) ?>" data-first-name="<?= html_escape($member->first_name) ?>" data-last-name="<?= html_escape($member->last_name) ?>" data-position-name="<?= html_escape($member->position_name) ?>" data-organization-name="<?= html_escape($member->organization_name) ?>" data-email="<?= html_escape($member->email) ?>" data-phone="<?= html_escape($member->phone) ?>" data-status="<?= (int)$member->status ?>">แก้ไข</button><form class="js-delete-member" method="post" action="<?= site_url('admin/members/delete/'.$member->id) ?>" data-name="<?= html_escape(trim($member->first_name.' '.$member->last_name)) ?>"><button class="btn btn-sm btn-outline-danger">ลบ</button></form></div></td>
                    </tr><?php endforeach; ?>
                    </tbody>
                </table></div>
                <?php if (count($members) >= 50): ?><p class="small text-secondary mb-0">แสดง 50 รายการล่าสุด กรุณาใช้ช่องค้นหาเพื่อค้นหาสมาชิกเพิ่มเติม</p><?php endif; ?>
            </div>
        </div>
        <div class="modal fade" id="memberModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><form method="post" id="member-form"><div class="modal-header member-modal-header"><h2 class="modal-title fs-5" id="member-modal-title">เพิ่มสมาชิก</h2><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="ปิด"></button></div><div class="modal-body"><div class="row g-3">
            <div class="col-md-3"><label class="form-label">คำนำหน้า</label><input class="form-control" name="title_name"></div><div class="col-md-4"><label class="form-label">ชื่อ *</label><input class="form-control" name="first_name" required></div><div class="col-md-5"><label class="form-label">นามสกุล *</label><input class="form-control" name="last_name" required></div>
            <div class="col-md-6"><label class="form-label">อีเมล *</label><input class="form-control" type="email" name="email" required></div><div class="col-md-6"><label class="form-label">โทรศัพท์</label><input class="form-control" name="phone"></div>
            <div class="col-md-6"><label class="form-label">ตำแหน่ง</label><input class="form-control" name="position_name"></div><div class="col-md-6"><label class="form-label">หน่วยงาน</label><input class="form-control" name="organization_name"></div>
            <div class="col-md-6"><label class="form-label">รหัสผ่าน <span id="password-required">*</span></label><input class="form-control" type="password" name="password" minlength="8" autocomplete="new-password"><div class="form-text" id="password-help">อย่างน้อย 8 ตัวอักษร</div></div><div class="col-md-6"><label class="form-label">สถานะ</label><select class="form-select" name="status"><option value="1">ใช้งาน</option><option value="0">ปิดใช้งาน</option></select></div>
        </div></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button><button class="btn btn-primary">บันทึกข้อมูล</button></div></form></div></div></div>
    </main>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script><script>
var memberForm=document.getElementById('member-form'),modalTitle=document.getElementById('member-modal-title'),baseStore=<?= json_encode(site_url('admin/members/store')) ?>,baseUpdate=<?= json_encode(site_url('admin/members/update/')) ?>;
document.getElementById('add-member').addEventListener('click',function(){memberForm.reset();memberForm.action=baseStore;modalTitle.textContent='เพิ่มสมาชิก';memberForm.password.required=true;document.getElementById('password-required').hidden=false;document.getElementById('password-help').textContent='อย่างน้อย 8 ตัวอักษร';});
document.querySelectorAll('.js-edit-member').forEach(function(button){button.addEventListener('click',function(){memberForm.reset();memberForm.action=baseUpdate+button.dataset.id;modalTitle.textContent='แก้ไขสมาชิก';['titleName','firstName','lastName','positionName','organizationName','email','phone','status'].forEach(function(key){memberForm.elements[key.replace(/[A-Z]/g,function(v){return '_'+v.toLowerCase()})].value=button.dataset[key];});memberForm.password.required=false;document.getElementById('password-required').hidden=true;document.getElementById('password-help').textContent='เว้นว่างหากไม่ต้องการเปลี่ยนรหัสผ่าน';});});
document.querySelectorAll('.js-delete-member').forEach(function(form){form.addEventListener('submit',function(e){e.preventDefault();Swal.fire({title:'ยืนยันการลบสมาชิก?',text:'ต้องการลบ "'+form.dataset.name+'" ใช่หรือไม่',icon:'warning',showCancelButton:true,confirmButtonText:'ลบข้อมูล',cancelButtonText:'ยกเลิก',confirmButtonColor:'#d94f45',reverseButtons:true}).then(function(result){if(result.isConfirmed)form.submit();});});});
</script>
<?php $this->load->view('admins/layouts/footer'); ?>
