// Test admin form submission
console.log('=== ADMIN FORM DEBUG ===');

// Check if form exists
const form = document.querySelector('#popup-form');
if (!form) {
    console.error('❌ Form not found!');
} else {
    console.log('✅ Form found');

    // Check form action
    console.log('Form action:', form.action);
    console.log('Form method:', form.method);

    // Check all color inputs
    const bgColor = document.querySelector('input[name="background_color"]');
    const textColor = document.querySelector('input[name="text_color"]');
    const ctaBg = document.querySelector('input[name="cta_background_color"]');
    const couponSelect = document.querySelector('select[name="coupon_id"]');
    const showCta = document.querySelector('input[name="show_cta"][type="checkbox"]');
    const showClose = document.querySelector('input[name="show_close_link"][type="checkbox"]');

    console.log('Background Color Input:', bgColor ? bgColor.value : 'NOT FOUND');
    console.log('Text Color Input:', textColor ? textColor.value : 'NOT FOUND');
    console.log('CTA BG Input:', ctaBg ? ctaBg.value : 'NOT FOUND');
    console.log('Coupon Select:', couponSelect ? couponSelect.value : 'NOT FOUND');
    console.log('Show CTA:', showCta ? showCta.checked : 'NOT FOUND');
    console.log('Show Close:', showClose ? showClose.checked : 'NOT FOUND');

    // Add submit listener
    form.addEventListener('submit', function (e) {
        console.log('🚀 FORM SUBMITTING!');
        console.log('Background color value:', bgColor.value);
        console.log('Coupon ID value:', couponSelect.value);
        console.log('Show CTA checked:', showCta.checked);
        console.log('Show Close checked:', showClose.checked);

        // Don't prevent default - let it submit
        // e.preventDefault();
    });

    console.log('✅ Submit listener added');
}
