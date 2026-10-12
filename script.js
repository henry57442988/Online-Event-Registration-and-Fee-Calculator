/* ============================================================
   Student Technology & Innovation Conference
   JavaScript: Validation, Dynamic Behaviour, Fee Calculation
   ============================================================ */

/* ---------- Constants (Charges) ---------- */
const REGISTRATION_FEE = 1000;   // KSh
const ATTENDANCE_PER_DAY = 500;  // KSh per day
const WORKSHOP_FEE = 750;        // KSh
const LUNCH_PER_DAY = 300;       // KSh per day
const FINAL_YEAR_DISCOUNT = 0.05; // 5% discount

/* ============================================================
   Function 1: validateForm()
   Validates all required fields before submission.
   Returns true if valid, false otherwise.
   ============================================================ */
function validateForm(event) {
    let isValid = true;

    // Clear all previous errors
    clearAllErrors();

    // 1. Full name cannot be empty
    const fullName = document.getElementById('fullName').value.trim();
    if (fullName === '') {
        showError('fullName', 'Full name is required.');
        isValid = false;
    }

    // 2. Admission number cannot be empty
    const admission = document.getElementById('admissionNumber').value.trim();
    if (admission === '') {
        showError('admissionNumber', 'Admission number is required.');
        isValid = false;
    }

    // 3. Email must be valid
    const email = document.getElementById('email').value.trim();
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (email === '') {
        showError('email', 'Email address is required.');
        isValid = false;
    } else if (!emailPattern.test(email)) {
        showError('email', 'Please enter a valid email address.');
        isValid = false;
    }

    // 4. Phone must be exactly 10 digits
    const phone = document.getElementById('phone').value.trim();
    const phonePattern = /^\d{10}$/;
    if (phone === '') {
        showError('phone', 'Phone number is required.');
        isValid = false;
    } else if (!phonePattern.test(phone)) {
        showError('phone', 'Phone number must contain exactly 10 digits.');
        isValid = false;
    }

    // 5. Year of study must be selected
    const year = document.getElementById('yearOfStudy').value;
    if (year === '') {
        showError('yearOfStudy', 'Please select your year of study.');
        isValid = false;
    }

    // 6. Participation category must be selected
    const category = document.getElementById('category').value;
    if (category === '') {
        showError('category', 'Please select a participation category.');
        isValid = false;
    }

    // 7. Number of days must be between 1 and 3
    const days = parseInt(document.getElementById('days').value, 10);
    if (isNaN(days) || days < 1 || days > 3) {
        showError('days', 'Number of days must be between 1 and 3.');
        isValid = false;
    }

    // Prevent submission if invalid
    if (!isValid) {
        event.preventDefault();
        return false;
    }

    return true;
}

/* ============================================================
   Function 2: calculateFee()
   Calculates the total fee based on user selections and
   updates the fee breakdown on the page.
   ============================================================ */
function calculateFee() {
    // --- Gather values ---
    const days = parseInt(document.getElementById('days').value, 10) || 0;
    const category = document.getElementById('category').value;
    const workshop = document.getElementById('workshop').value;
    const meal = document.getElementById('meal').value;
    const year = document.getElementById('yearOfStudy').value;

    // --- Registration fee (always charged) ---
    const regFee = REGISTRATION_FEE;

    // --- Attendance fee: days × 500 ---
    const attendanceFee = days * ATTENDANCE_PER_DAY;

    // --- Workshop fee: only if category is workshop_participant
    //     AND a workshop other than "none" is selected ---
    let workshopFee = 0;
    if (category === 'workshop_participant' && workshop !== 'none') {
        workshopFee = WORKSHOP_FEE;
    }

    // --- Meal fee: days × 300 if catering selected ---
    let mealFee = 0;
    if (meal === 'yes') {
        mealFee = days * LUNCH_PER_DAY;
    }

    // --- Subtotal ---
    let subtotal = regFee + attendanceFee + workshopFee + mealFee;

    // --- Final-year discount (5%) ---
    let discount = 0;
    if (year === '4') {
        discount = subtotal * FINAL_YEAR_DISCOUNT;
    }

    const total = subtotal - discount;

    // --- Update DOM ---
    document.getElementById('regFee').textContent = 'KSh ' + regFee.toLocaleString();
    document.getElementById('attendanceFee').textContent = 'KSh ' + attendanceFee.toLocaleString();
    document.getElementById('workshopFee').textContent = 'KSh ' + workshopFee.toLocaleString();
    document.getElementById('mealFee').textContent = 'KSh ' + mealFee.toLocaleString();

    // Discount line
    const discountLine = document.getElementById('discountLine');
    if (discount > 0) {
        discountLine.style.display = 'block';
        document.getElementById('discountAmount').textContent =
            '- KSh ' + discount.toLocaleString(undefined, { maximumFractionDigits: 2 });
    } else {
        discountLine.style.display = 'none';
    }

    document.getElementById('totalFee').textContent =
        'KSh ' + total.toLocaleString(undefined, { maximumFractionDigits: 2 });

    return total;
}

/* ============================================================
   Function 3: handleCategoryChange()
   Dynamically shows/hides workshop options and displays a
   package message based on the selected participation category.
   ============================================================ */
function handleCategoryChange() {
    const category = document.getElementById('category').value;
    const workshopGroup = document.getElementById('workshopGroup');
    const workshopSelect = document.getElementById('workshop');
    const packageMessage = document.getElementById('packageMessage');

    if (category === 'workshop_participant') {
        // Show workshop options
        workshopGroup.style.display = 'block';
        workshopSelect.disabled = false;
        packageMessage.style.display = 'block';
        packageMessage.textContent =
            '📦 Selected Package: Workshop Participant – includes a specialised workshop.';
    } else if (category === 'conference_only') {
        // Hide workshop options and reset
        workshopGroup.style.display = 'none';
        workshopSelect.value = 'none';
        workshopSelect.disabled = true;
        packageMessage.style.display = 'block';
        packageMessage.textContent =
            '📦 Selected Package: Conference Only – no workshop included.';
    } else if (category === 'presenter') {
        // Presenters can also attend workshops (optional)
        workshopGroup.style.display = 'block';
        workshopSelect.disabled = false;
        packageMessage.style.display = 'block';
        packageMessage.textContent =
            '📦 Selected Package: Presenter – you may also join a workshop.';
    } else {
        workshopGroup.style.display = 'none';
        workshopSelect.value = 'none';
        workshopSelect.disabled = true;
        packageMessage.style.display = 'none';
    }

    // Recalculate fee after category change
    calculateFee();
}

/* ============================================================
   Helper Functions: Error display and clearing
   ============================================================ */
function showError(fieldId, message) {
    const errorSpan = document.getElementById('error-' + fieldId);
    if (errorSpan) {
        errorSpan.textContent = message;
    }
}

function clearError(fieldId) {
    const errorSpan = document.getElementById('error-' + fieldId);
    if (errorSpan) {
        errorSpan.textContent = '';
    }
}

function clearAllErrors() {
    const errorSpans = document.querySelectorAll('.error');
    errorSpans.forEach(function (span) {
        span.textContent = '';
    });
}

/* ============================================================
   Function: resetForm()
   Resets the form and recalculates the fee.
   ============================================================ */
function resetForm() {
    // Use setTimeout to allow the native reset to happen first
    setTimeout(function () {
        clearAllErrors();
        document.getElementById('workshopGroup').style.display = 'none';
        document.getElementById('packageMessage').style.display = 'none';
        document.getElementById('discountLine').style.display = 'none';
        calculateFee();
    }, 0);
}

/* ============================================================
   Initialisation: run on page load
   ============================================================ */
document.addEventListener('DOMContentLoaded', function () {
    // Set initial state
    handleCategoryChange();
    calculateFee();
});