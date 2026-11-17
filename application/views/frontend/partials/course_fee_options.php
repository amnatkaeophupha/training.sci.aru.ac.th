<section class="panel course-fees" aria-labelledby="course-fees-title">
    <div class="course-fees__heading">
        <div>
            <span class="course-fees__eyebrow">อัตราค่าสมัคร</span>
            <h3 id="course-fees-title">เงื่อนไขราคาสำหรับทั้งใบสมัคร</h3>
            <p class="course-fees__intro">ตรวจสอบเงื่อนไขที่ตรงกับผู้สมัคร ก่อนเลือกราคาในขั้นตอนสมัครอบรม</p>
        </div>
        <span class="course-fees__count"><?= number_format(count($fee_options)) ?> เงื่อนไข</span>
    </div>
    <ul class="course-fees__options">
        <?php foreach($fee_options as $fee_index=>$option): $is_free=(float)$option['amount']===0.0; ?>
            <li class="course-fees__option <?= $is_free?'course-fees__option--free':'' ?>">
                <div>
                    <span class="course-fees__option-label">เงื่อนไขที่ <?= $fee_index+1 ?></span>
                    <h4><?= html_escape($option['label']) ?></h4>
                </div>
                <div class="course-fees__price">
                    <strong><?= $is_free?'ไม่มีค่าใช้จ่าย':number_format((float)$option['amount'],2) ?></strong>
                    <span><?= $is_free?'สำหรับเงื่อนไขนี้':'บาท / คน' ?></span>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
    <div class="course-fees__footer">
        <div class="course-fees__note">
            <strong>เลือก 1 เงื่อนไขสำหรับทั้งใบสมัคร</strong>
            <p>ผู้เข้าอบรมทุกคนในใบสมัครใช้ราคาเดียวกัน ยอดรวมคำนวณจากราคาต่อคน × จำนวนผู้เข้าอบรม</p>
        </div>
        <?php if (!empty($batches)): ?>
            <a class="btn course-fees__register" href="#register">ไปยังส่วนสมัครอบรม <span aria-hidden="true">→</span></a>
        <?php else: ?>
            <span class="course-fees__unavailable">ยังไม่มีรอบที่เปิดรับสมัคร</span>
        <?php endif; ?>
    </div>
</section>
