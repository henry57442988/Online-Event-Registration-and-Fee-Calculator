<?php
/* ============================================================
   Student Technology & Innovation Conference
   process.php – Server-side form processing and confirmation
   ============================================================ */

// ---------- 1. Receive submitted values using $_POST ----------
$fullName       = isset($_POST['fullName'])       ? trim($_POST['fullName'])       : '';
$admissionNo    = isset($_POST['admissionNumber'])? trim($_POST['admissionNumber']): '';
$email          = isset($_POST['email'])          ? trim($_POST['email'])          : '';
$phone          = isset($_POST['phone'])          ? trim($_POST['phone'])          : '';
$yearOfStudy    = isset($_POST['yearOfStudy'])    ? trim($_POST['yearOfStudy'])    : '';
$category       = isset($_POST['category'])       ? trim($_POST['category'])       : '';
$days           = isset($_POST['days'])           ? (int)$_POST['days']            : 0;
$workshop       = isset($_POST['workshop'])       ? trim($_POST['workshop'])       : 'none';
$meal           = isset($_POST['meal'])           ? trim($_POST['meal'])           : 'no';

// ---------- 2. Check essential values have been received ----------
$errors = [];

if ($fullName === '') {
    $errors[] = 'Full name is missing.';
}
if ($admissionNo === '') {
    $errors[] = 'Admission number is missing.';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'A valid email address is required.';
}
if (!preg_match('/^\d{10}$/', $phone)) {
    $errors[] = 'Phone number must contain exactly 10 digits.';
}
if ($yearOfStudy === '') {
    $errors[] = 'Year of study is missing.';
}
if ($category === '') {
    $errors[] = 'Participation category is missing.';
}
if ($days < 1 || $days > 3) {
    $errors[] = 'Number of conference days must be between 1 and 3.';
}

// ---------- 3. Server-side validation of category ----------
$validCategories = ['conference_only', 'workshop_participant', 'presenter'];
if (!in_array($category, $validCategories)) {
    $errors[] = 'Invalid participation category.';
}

// ---------- 4. Independently calculate the total fee ----------
// Constants (must match JavaScript)
define('REGISTRATION_FEE', 1000);
define('ATTENDANCE_PER_DAY', 500);
define('WORKSHOP_FEE', 750);
define('LUNCH_PER_DAY', 300);
define('FINAL_YEAR_DISCOUNT', 0.05);

$regFee         = REGISTRATION_FEE;
$attendanceFee  = $days * ATTENDANCE_PER_DAY;
$workshopFee    = 0;
$mealFee        = 0;

// Workshop fee only if workshop participant and a workshop is chosen
if ($category === 'workshop_participant' && $workshop !== 'none' && $workshop !== '') {
    $workshopFee = WORKSHOP_FEE;
}

// Meal fee only if catering selected
if ($meal === 'yes') {
    $mealFee = $days * LUNCH_PER_DAY;
}

$subtotal = $regFee + $attendanceFee + $workshopFee + $mealFee;

// Final-year discount (5%)
$discount = 0;
if ($yearOfStudy === '4') {
    $discount = $subtotal * FINAL_YEAR_DISCOUNT;
}

$total = $subtotal - $discount;

// ---------- 5. Friendly labels for display ----------
$categoryLabels = [
    'conference_only'      => 'Conference Only',
    'workshop_participant' => 'Workshop Participant',
    'presenter'            => 'Presenter'
];

$workshopLabels = [
    'none'            => 'None',
    'web_development' => 'Web Development',
    'mobile_apps'     => 'Mobile App Development',
    'data_science'    => 'Data Science & AI',
    'cybersecurity'   => 'Cybersecurity'
];

$categoryDisplay = isset($categoryLabels[$category]) ? $categoryLabels[$category] : $category;
$workshopDisplay = isset($workshopLabels[$workshop]) ? $workshopLabels[$workshop] : $workshop;
$mealDisplay     = ($meal === 'yes') ? 'Yes (Lunch)' : 'No';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Confirmation</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .confirmation {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 700px;
            margin: 40px auto;
        }
        .confirmation h2 {
            color: #2e7d32;
            text-align: center;
            margin-bottom: 20px;
        }
        .confirmation .error-box {
            background: #ffebee;
            color: #c62828;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .confirmation table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .confirmation table th,
        .confirmation table td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .confirmation table th {
            width: 45%;
            color: #0d47a1;
        }
        .confirmation .total-row {
            font-weight: bold;
            font-size: 1.2rem;
            color: #0d47a1;
            border-top: 2px solid #0d47a1;
        }
        .confirmation .discount-row {
            color: #2e7d32;
        }
        .confirmation .thank-you {
            text-align: center;
            margin-top: 20px;
            font-style: italic;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            background: #1976d2;
            color: #fff;
            padding: 10px 25px;
            border-radius: 5px;
            text-decoration: none;
        }
        .back-link:hover { background: #0d47a1; }
    </style>
</head>
<body>

    <header>
        <div class="container">
            <h1>Student Technology &amp; Innovation Conference</h1>
            <p class="tagline">Registration Confirmation</p>
        </div>
    </header>

    <nav>
        <div class="container">
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="register.html">Register</a></li>
            </ul>
        </div>
    </nav>

    <main class="container">
        <div class="confirmation">

            <?php if (!empty($errors)): ?>
                <!-- ===== Error Display ===== -->
                <div class="error-box">
                    <h2>⚠ Registration Failed</h2>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="register.html" class="back-link">Go Back to Registration</a>
                </div>

            <?php else: ?>
                <!-- ===== Successful Registration ===== -->
                <h2>✅ REGISTRATION SUCCESSFUL</h2>

                <table>
                    <tr>
                        <th>Participant:</th>
                        <td><?php echo htmlspecialchars($fullName); ?></td>
                    </tr>
                    <tr>
                        <th>Admission Number:</th>
                        <td><?php echo htmlspecialchars($admissionNo); ?></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td><?php echo htmlspecialchars($email); ?></td>
                    </tr>
                    <tr>
                        <th>Phone:</th>
                        <td><?php echo htmlspecialchars($phone); ?></td>
                    </tr>
                    <tr>
                        <th>Year of Study:</th>
                        <td>Year <?php echo htmlspecialchars($yearOfStudy); ?></td>
                    </tr>
                    <tr>
                        <th>Participation Category:</th>
                        <td><?php echo htmlspecialchars($categoryDisplay); ?></td>
                    </tr>
                    <tr>
                        <th>Conference Days:</th>
                        <td><?php echo htmlspecialchars($days); ?></td>
                    </tr>
                    <tr>
                        <th>Workshop:</th>
                        <td><?php echo htmlspecialchars($workshopDisplay); ?></td>
                    </tr>
                    <tr>
                        <th>Meals:</th>
                        <td><?php echo htmlspecialchars($mealDisplay); ?></td>
                    </tr>
                </table>

                <h3>Fee Breakdown</h3>
                <table>
                    <tr>
                        <th>Registration Fee:</th>
                        <td>KSh <?php echo number_format($regFee, 2); ?></td>
                    </tr>
                    <tr>
                        <th>Attendance Fee (<?php echo $days; ?> day(s) × KSh 500):</th>
                        <td>KSh <?php echo number_format($attendanceFee, 2); ?></td>
                    </tr>
                    <tr>
                        <th>Workshop Fee:</th>
                        <td>KSh <?php echo number_format($workshopFee, 2); ?></td>
                    </tr>
                    <tr>
                        <th>Meal Fee (<?php echo $days; ?> day(s) × KSh 300):</th>
                        <td>KSh <?php echo number_format($mealFee, 2); ?></td>
                    </tr>
                    <?php if ($discount > 0): ?>
                    <tr class="discount-row">
                        <th>Final-Year Discount (5%):</th>
                        <td>- KSh <?php echo number_format($discount, 2); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr class="total-row">
                        <th>TOTAL AMOUNT PAYABLE:</th>
                        <td>KSh <?php echo number_format($total, 2); ?></td>
                    </tr>
                </table>

                <p class="thank-you">
                    Thank you for registering for the<br>
                    <strong>Student Technology and Innovation Conference</strong>.
                </p>

                <div style="text-align:center;">
                    <a href="index.html" class="back-link">Return to Home</a>
                </div>

            <?php endif; ?>

        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2026 Student Technology and Innovation Conference. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>