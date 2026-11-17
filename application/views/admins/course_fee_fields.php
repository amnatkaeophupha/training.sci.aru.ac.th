<?php
$fee_mode=isset($edit_course->fee_mode)?$edit_course->fee_mode:'fixed';
$pricing_rows=isset($fee_options)?$fee_options:array();
?>
<div class="col-12">
    <label class="form-label fw-bold" for="fee_mode">รูปแบบค่าสมัคร</label>
    <select class="form-select" id="fee_mode" name="fee_mode">
        <option value="fixed" <?= $fee_mode==='fixed'?'selected':'' ?>>ราคาเดียว</option>
        <option value="options" <?= $fee_mode==='options'?'selected':'' ?>>ราคาตามเงื่อนไข</option>
    </select>
    <div class="form-text">ผู้สมัครเลือกเงื่อนไขครั้งเดียว ทุกคนในใบสมัครใช้ราคาเดียวกัน ใบสมัครเดิมคงราคาที่บันทึกไว้</div>
    <section id="fee-options-panel" class="border rounded p-3 mt-3" <?= $fee_mode==='fixed'?'hidden':'' ?>>
        <h3 class="h6">เงื่อนไขค่าสมัคร</h3>
        <div id="fee-options-rows">
            <?php foreach($pricing_rows as $i=>$option): ?>
                <div class="row g-2 align-items-end border-bottom pb-3 mb-3 fee-option-row" data-saved="<?= (int)$option['id'] ?>">
                    <input type="hidden" data-field="id" name="fee_options[<?= $i ?>][id]" value="<?= (int)$option['id'] ?>">
                    <div class="col-md-5"><label class="form-label">ชื่อเงื่อนไข</label><input class="form-control" data-field="label" name="fee_options[<?= $i ?>][label]" maxlength="255" value="<?= html_escape($option['label']) ?>"></div>
                    <div class="col-md-3"><label class="form-label">ราคา/คน (บาท)</label><input class="form-control" data-field="amount" name="fee_options[<?= $i ?>][amount]" type="number" min="0" max="99999999.99" step="0.01" value="<?= html_escape($option['amount']) ?>"></div>
                    <div class="col-md-2"><label class="form-label">สถานะ</label><select class="form-select" data-field="is_active" name="fee_options[<?= $i ?>][is_active]"><option value="1" <?= (int)$option['is_active']===1?'selected':'' ?>>เปิดใช้งาน</option><option value="0" <?= (int)$option['is_active']===0?'selected':'' ?>>ปิดใช้งาน</option></select></div>
                    <div class="col-md-2 d-flex flex-wrap gap-1"><button class="btn btn-outline-secondary" type="button" data-move="up" aria-label="เลื่อนขึ้น">↑</button><button class="btn btn-outline-secondary" type="button" data-move="down" aria-label="เลื่อนลง">↓</button><?php if (empty($option['id'])): ?><button class="btn btn-outline-danger" type="button" data-remove>นำออก</button><?php endif; ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="btn btn-outline-primary" type="button" id="add-fee-option">+ เพิ่มเงื่อนไขราคา</button>
        <div class="form-text">ตัวอย่าง: เด็กอายุไม่เกิน 7 ปี ราคา 200 บาท / อายุมากกว่า 7 ปี ราคา 300 บาท ระบบไม่ตรวจอายุอัตโนมัติ เงื่อนไขที่บันทึกแล้วใช้การปิดใช้งานแทนลบ</div>
    </section>
</div>
<template id="fee-option-template">
    <div class="row g-2 align-items-end border-bottom pb-3 mb-3 fee-option-row" data-saved="0">
        <input type="hidden" data-field="id" value="0">
        <div class="col-md-5"><label class="form-label">ชื่อเงื่อนไข</label><input class="form-control" data-field="label" maxlength="255"></div>
        <div class="col-md-3"><label class="form-label">ราคา/คน (บาท)</label><input class="form-control" data-field="amount" type="number" min="0" max="99999999.99" step="0.01" value="0.00"></div>
        <div class="col-md-2"><label class="form-label">สถานะ</label><select class="form-select" data-field="is_active"><option value="1">เปิดใช้งาน</option><option value="0">ปิดใช้งาน</option></select></div>
        <div class="col-md-2 d-flex flex-wrap gap-1"><button class="btn btn-outline-secondary" type="button" data-move="up" aria-label="เลื่อนขึ้น">↑</button><button class="btn btn-outline-secondary" type="button" data-move="down" aria-label="เลื่อนลง">↓</button><button class="btn btn-outline-danger" type="button" data-remove>นำออก</button></div>
    </div>
</template>
