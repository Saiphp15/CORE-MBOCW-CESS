<?php
session_start();
require_once 'config/db.php';

// Fetch required dropdown values
$localAuthorityTypes = $conn->query("SELECT id, name FROM local_authority_types")->fetch_all(MYSQLI_ASSOC);
$authority_departments = $conn->query("SELECT id, name FROM authority_departments")->fetch_all(MYSQLI_ASSOC);
$local_authority_subdepartments = $conn->query("SELECT id, name FROM authority_subdepartments")->fetch_all(MYSQLI_ASSOC);
$districts = $conn->query("SELECT id, name FROM districts")->fetch_all(MYSQLI_ASSOC);

$old = $_SESSION['old'] ?? [];
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';

if (isset($_SESSION['success']) && !empty($_SESSION['success'])) {
    unset($_SESSION['error']);
    unset($_SESSION['old']);
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Maharashtra Building And Other Construction Worker's Welfare Board official portal for CESS, schemes, and worker services.">
    <meta name="keywords" content="MBOCWW, Maharashtra, Construction Worker Welfare, CESS Portal, Government Portal">
    <meta name="author" content="MBOCWW Board">
    <meta name="csrf-token" content="VMGEYmacOGXZTpQsTWlDZ1UdSN6chYRFio7HncOk">
    <title>MBOCWCESS Portal</title>
    <!-- ================== Fevicons ==================-->
    <link rel="apple-touch-icon" sizes="180x180" href="assets/img/favicon_io/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicon_io/favicon-16x16.png">
    <link rel="manifest" href="assets/img/favicon_io//site.webmanifest">

    <link href="https://fonts.googleapis.com/css2?family=Mukta:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Mukta', sans-serif;
        }

        /* Main Header */
        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            padding: 10px 20px;
            border-bottom: 3px solid #B22222;
            background-color: #ffffff;
        }

        .header-left,
        .header-right {
            display: flex;
            align-items: center;
        }

        .header-left img,
        .header-right img {
            height: 70px;
            margin: 5px 10px;
        }

        .header-center {
            flex: 1;
            text-align: center;
        }

        .header-center h1 {
            font-size: 20px;
            margin: 0;
            color: #800000;
        }

        .header-center h2 {
            font-size: 15px;
            margin: 5px 0 0;
            color: #b03a2e;
        }

        /* Subheader Navigation */
        .subheader {
            background-color: #f9ffcc;
            padding: 5px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .subheader-left img {
            height: 50px;
            margin-right: 10px;
        }

        .subheader-title {
            font-weight: bold;
            font-size: 18px;
        }

        .subheader-menu {
            display: flex;
            gap: 20px;
            font-size: 16px;
        }

        .subheader-menu a {
            margin: 5px 0;
            text-decoration: none;
            color: #222;
            transition: color 0.3s;
        }

        .subheader-menu a:hover {
            color: #f57c00;
        }

        /* Scrolling Notice (Optional) */
        .scrolling-banner {
            background-color: #ff6d00;
            color: white;
            font-size: 15px;
            padding: 8px 20px;
            white-space: nowrap;
            overflow: hidden;
        }

        .scrolling-banner span {
            display: inline-block;
            animation: scroll-left 30s linear infinite;
            /* ⬅️ changed from 15s to 30s */
        }

        @keyframes scroll-left {
            0% {
                transform: translateX(100%);
            }

            100% {
                transform: translateX(-100%);
            }
        }

        .carousel img {
            height: 500px;
            object-fit: cover;
        }

        @media (max-width: 768px) {

            .header-left img,
            .header-right img {
                height: 50px;
            }

            .header-center h1 {
                font-size: 16px;
            }

            .header-center h2 {
                font-size: 13px;
            }

            .subheader-menu {
                flex-direction: column;
                align-items: flex-start;
            }

            .carousel img {
                height: 300px;
            }
        }

        #backToTopBtn {
            display: none;
            position: fixed;
            bottom: 40px;
            right: 30px;
            z-index: 99;
            font-size: 22px;
            background-color: #f57c00;
            color: white;
            border: none;
            outline: none;
            padding: 12px 16px;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: opacity 0.3s, transform 0.3s;
        }

        #backToTopBtn:hover {
            background-color: #e65100;
        }
    </style>
</head>

<body>
    <!-- Main Header -->
    <div class="main-header">
        <div class="header-left">
            <img src="assets\img\homepage\mahaMBOCWLogo.jpg" loading="lazy" alt="Maharashtra Map Logo">
            <img src="assets\img\homepage\mbocw-logo.png" loading="lazy" alt="Board Logo">
        </div>
        <div class="header-center">
            <h1>महाराष्ट्र इमारत व इतर बांधकाम कामगार कल्याणकारी मंडळ</h1>
            <h2>Maharashtra Building And Other Construction Worker's Welfare Board</h2>
        </div>
        <div class="header-right">
            <img src="assets\img\homepage\Maharashtra-state-copy.png" loading="lazy" alt="Gov of Maharashtra Logo">
            <img src="assets\img\homepage\Ashok-Symbol.png" loading="lazy" alt="Indian Emblem">
        </div>
    </div>

    <!-- Subheader / Menu -->
    <div class="subheader">
        <div class="subheader-left">
            <img src="assets\img\homepage\g20.png" loading="lazy" alt="G20 Logo">
            <img src="assets\img\homepage\akam.png" loading="lazy" alt="Azadi Logo">
        </div>
        <div class="subheader-title">
            MBOCWW Board CESS Portal<br><small>MAHARASHTRA GOVERNMENT</small>
        </div>
        <div class="subheader-menu">
            <a href="#">Home</a>
            <a href="#">About Us</a>
            <a href="#">Functionaries</a>
            <a href="#">MAHGOV Resolution</a>
            <a href="#">Schemes</a>
            <a href="#">Contact Us</a>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        </div>
    </div>

    <!-- Optional Scrolling Banner -->
    <div class="scrolling-banner">
        <span>महाराष्ट्र इमारत व इतर बांधकाम कामगार कल्याणकारी मंडळाने सेस रक्कम जमा करण्याकरिता सदरचे अधिकृत वेबपोर्टल तयार केले आहे. तरी उपकर अदा करणारे, अंमलबजावणी करणाऱ्या ऐजंसी व सरकारी विभागांना विनंती करण्यात येत आहे की ऑनलाईन पद्धतीने सेस भरण्याकरिता सदर पोर्टलचा वापर करावा.</span>
        <span> This is the official web portal of MBOCWW Board to collect the BOCW CESS Amount. All Cess Payee, Implementing Agencies and Government Departments are kindly requested to use this portal to complete the CESS payment through online mode.</span>
    </div>
    <section class="py-5 bg-light">
        <div class="container mb-3">
            <h2 class="text-center fw-bold mb-4">Local Authority With CAFO Registration</h2>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            <form action="save-register-form.php" method="POST" id="cafoRegistrationForm" class="row g-4">
                <!-- Local Authority Details -->
                <h5 class="text-primary">Local Authority Details</h5>
                <div class="col-md-6">
                    <label class="form-label">Local Authority Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($old['local_authority_name'] ?? '') ?>" name="local_authority_name" id="local_authority_name" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Local Authority Type <span class="text-danger">*</span></label>
                    <select class="form-select" name="local_authority_type" id="local_authority_type" required>
                        <option value="">Select Local Authority Type</option>
                        <?php foreach ($localAuthorityTypes as $type): ?>
                            <option value="<?= $type['id'] ?>"
                                <?= ($old['local_authority_type'] ?? '') == $type['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($type['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label>Authority Department</label>
                        <select name="department_id" id="department_id" class="form-control">
                            <option value="">-- Select Department --</option>
                            <?php foreach ($authority_departments as $department): ?>
                                <option value="<?= $department['id'] ?>"
                                    <?= (isset($_SESSION['old_values']['department_id'])
                                        && $_SESSION['old_values']['department_id'] == $department['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($department['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Authority Sub Department</label>
                        <select name="subdepartment_id" id="subdepartment_id" class="form-control">
                            <option value="">-- Select Subdepartment --</option>
                            <?php foreach ($local_authority_subdepartments as $subdept): ?>
                                <option value="<?= $subdept['id'] ?>"
                                    <?= (isset($_SESSION['old_values']['subdepartment_id'])
                                        && $_SESSION['old_values']['subdepartment_id'] == $subdept['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($subdept['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">PAN Card No <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($old['pan_no'] ?? '') ?>" name="authority_pancard" id="pancard" maxlength="10" placeholder="Enter 10 character PAN" required>
                    <div id="panError" class="error"></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">GSTN </label>
                    <input type="text" class="form-control " value="<?= htmlspecialchars($old['gstn'] ?? '') ?>" name="authority_gstn" id="gstn" maxlength="16" placeholder="Enter 15 character GSTN">
                    <div id="gstnError" class="error"></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">State <span class="text-danger">*</span></label>
                    <select class="form-select stateChange" name="authority_state" id="authority_state" required>
                        <option value="">Select State</option>
                        <option value="14" selected>Maharashtra</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">District <span class="text-danger">*</span></label>
                    <select class="form-select districtChange" name="authority_district" id="authority_district" required>
                        <option value="">Select District</option>
                        <?php foreach ($districts as $district): ?>
                            <option value="<?= htmlspecialchars($district['id']) ?>"><?= htmlspecialchars($district['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Taluka <span class="text-danger">*</span></label>
                    <select class="form-select talukaChange" name="authority_taluka" id="authority_taluka" required>
                        <option value="">Select Taluka</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Village <span class="text-danger">*</span></label>
                    <select class="form-select villageChange" name="authority_village" id="authority_village" required>
                        <option value="">Select Village</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Address <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="authority_address" id="authority_address" rows="2" placeholder="Enter Address" required><?= htmlspecialchars($old['cafo_address'] ?? '') ?> </textarea>
                </div>

                <!-- Divider -->
                <hr class="my-4">

                <!-- CAFO Personal Details -->
                <h5 class="text-primary">CAFO Personal Details</h5>
                <p><strong>Note:</strong>Registration with the Local Authority under CAFO will not be processed until both the email address and mobile number have been verified</p>
                <div class="col-md-6">
                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" value="<?= htmlspecialchars($old['cafo_name'] ?? '') ?>" class="form-control" name="cafo_name" id="cafo_name" placeholder="Enter Full Name" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="email" value="<?= htmlspecialchars($old['cafo_email'] ?? '') ?>" class="form-control" id="cafo_email" name="cafo_email" placeholder="Enter Cafo Email" required>
                        <button class="btn btn-outline-secondary" type="button" id="verifyEmailBtn">Verify</button>
                    </div>
                    <div class="input-group d-none email_verifcation_code" id="div_email_verifcation_code">
                        <input type="text" value="<?= htmlspecialchars($old['email_verifcation_code'] ?? '') ?>" class="form-control" id="cafo_email_verifcation_code" name="cafo_email_verifcation_code" placeholder="Enter Verification code">
                        <button class="btn btn-outline-secondary" type="button" id="email_verifcation_button">Verify Code</button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="tel" value="<?= htmlspecialchars($old['cafo_mobile'] ?? '') ?>" class="form-control numeric" id="cafo_mobile" name="cafo_mobile" placeholder="Enter Mobile No" maxlength="10" required>
                        <button class="btn btn-outline-secondary" type="button" id="verifyMobileBtn">Verify</button>
                    </div>
                    <div class="input-group d-none mobile_verifcation_code" id="div_mobile_verifcation_code">
                        <input type="text" value="<?= htmlspecialchars($old['mobile_verifcation_code'] ?? '') ?>" class="form-control" id="mobile_verifcation_code" name="mobile_verifcation_code" placeholder="Enter Verification code">
                        <button class="btn btn-outline-secondary" type="button" id="mobile_verifcation_button">Verify Code</button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Gender <span class="text-danger">*</span></label>
                    <select class="form-select" name="cafo_gender" id="cafo_gender">
                        <option value="">Select Gender</option>
                        <option value="M" <?= ($old['cafo_gender'] ?? '') == 'M' ? 'selected' : '' ?>>Male</option>
                        <option value="F" <?= ($old['cafo_gender'] ?? '') == 'F' ? 'selected' : '' ?>>Female</option>
                        <option value="O" <?= ($old['cafo_gender'] ?? '') == 'O' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Aadhaar No <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($old['aadhaar_no'] ?? '') ?>" name="cafo_aadhaar" id="aadhaar" maxlength="12" placeholder="Enter 12 digit Aadhaar" required>
                    <div id="aadhaarError" class="error"></div>
                </div>
                <div class="col-12 text-center">
                    <button type="submit" class="btn btn-primary px-4 py-2 register-button">Register</button>
                    <button type="reset" class="btn btn-warning">Reset</button>
                </div>
            </form>
        </div>
        <!-- Bottom Decorative Strip -->
        <img src="assets/img/homepage/about-footer.png" loading="lazy" alt="Registration Form Footer" class="img-fluid">
    </section>
    <!-- Footer Section -->
    <footer style="background-color: #2c4a63; color: white; text-align: center; padding: 30px 20px;">
        <h4 style="margin: 0; font-weight: 600;">Terms & Conditions</h4>
        <p style="margin: 5px 0 15px;">Terms & Conditions</p>
        <p style="margin: 0; font-size: 14px;">
            © Content Owned by Maharashtra Building And Other Construction Workers Welfare Board.
        </p>
    </footer>
    <!-- Back to Top Button -->
    <button onclick="scrollToTop()" id="backToTopBtn" title="Go to top">↑</button>
    <!-- use a version range instead of a specific version -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3/dist/jquery.min.js"></script>
    <script type="text/javascript">
        // Show button on scroll
        window.onscroll = function() {
            const btn = document.getElementById("backToTopBtn");
            if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
                btn.style.display = "block";
            } else {
                btn.style.display = "none";
            }
        };

        // Scroll to top smoothly
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    </script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            var emailVerification = false;
	        var moblieVerification = false;
            $('.register-button').hide();
            function checkVerification() {
            if (emailVerification && moblieVerification) {
                $('.register-button').show();
            } else {
                $('.register-button').hide();
            }
        }
            // Aadhaar validation
            $('#aadhaar').on('input', function() {
                const aadhaar = $(this).val();
                const aadhaarPattern = /^\d{12}$/;
                if (!aadhaarPattern.test(aadhaar)) {
                    $('#aadhaarError').text('Aadhaar must be a 12-digit number.').css('color', 'red');
                } else {
                    $('#aadhaarError').text('');
                }
            });

            // PAN validation
            $('#pancard').on('input', function() {
                const pan = $(this).val().toUpperCase();
                const panPattern = /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/;
                if (!panPattern.test(pan)) {
                    $('#panError').text('PAN must be in format: AAAAA9999A.').css('color', 'red');
                } else {
                    $('#panError').text('');
                }
            });

            // GSTN validation
            $('#gstn').on('input', function() {
                const gstn = $(this).val().toUpperCase();
                const gstnPattern = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/;
                if (gstn && !gstnPattern.test(gstn)) {
                    $('#gstnError').text('GSTN must be a valid 15-character code.').css('color', 'red');
                } else {
                    $('#gstnError').text('');
                }
            });

            // Allow only numeric input for specific fields
            $('.numeric').on('input', function(event) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });

            $('.districtChange').on('change', function() {
                var district_id = this.value;
                if (!district_id) return;
                fetch("get-taluka.php?district_id=" + district_id, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => {
                        if (!res.ok) throw new Error("Network response was not ok");
                        return res.json();
                    })
                    .then(data => {
                        $('.talukaChange').empty();
                        var talukahmtl = '<option value="">Select taluka</option>';
                        data.forEach(sub => {
                            talukahmtl += `<option value="${sub.id}">${sub.name}</option>`;
                        });
                        $('.talukaChange').append(talukahmtl);
                    })
                    .catch(err => {
                        console.error('Fetch error:', err);
                        alert('Could not load talukas. See console for details.');
                    });
            });

            $('.talukaChange').on('change', function() {
                var taluka_id = this.value;
                if (!taluka_id) return;
                fetch("get-village.php?taluka_id=" + taluka_id, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => {
                        if (!res.ok) throw new Error("Network response was not ok");
                        return res.json();
                    })
                    .then(data => {
                        $('.villageChange').empty();
                        var villagehmtl = '<option value="">Select village</option>';
                        data.forEach(sub => {
                            villagehmtl += `<option value="${sub.id}">${sub.name}</option>`;
                        });
                        $('.villageChange').append(villagehmtl);
                    })
                    .catch(err => {
                        console.error('Fetch error:', err);
                        alert('Could not load villages. See console for details.');
                    });
            });

            $('#department_id').on('change', function() {
                const departmentId = $(this).val();
                if (departmentId) {
                    $.get('dashboard/get-types.php?department_id=' + departmentId, function(data) {
                        $('#subdepartment_id').html(data);
                    });
                } else {
                    $('#subdepartment_id').html('<option value="">-- Select Subdepartment --</option>');
                }
            });


            $('#verifyEmailBtn').on('click', function() {
                let email = $('#cafo_email').val();
                let cfo_name = $('#cafo_name').val();
                let $thisButton = $(this);
                if (!email || !email.includes('@') || !email.includes('.')) {
                    alert('Please enter a valid email address.');
                    return;
                }
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: 'email_verification.php',
                    method: 'POST',
                    data: {
                        action:'email_verification_email_send',
                        email: email,
                        full_name: cfo_name ? cfo_name : 'User'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#cafo_email').prop('readonly', true);
                            $('#div_email_verifcation_code').removeClass('d-none');
                        } else {
                            console.log(response);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 400) {
                            let errors = xhr.responseJSON.error;
                            alert(errors)
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            alert(xhr.responseJSON.message);
                        }
                    }
                });
            });

            $('#email_verifcation_button').on('click', function() {
                let code = $('#cafo_email_verifcation_code').val();
                let email = $('#cafo_email').val();
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: 'email_verification.php',
                    method: 'POST',
                    data: {
                        action:'verify_email',
                        code: code,
                        email: email
                    },
                    success: function(response) {
                        if (response.success) {
                            emailVerification = true;
                            checkVerification();
                            $('#div_email_verifcation_code').addClass('d-none');
                            $('#verifyEmailBtn').removeClass('btn-outline-secondary').text('Verified').addClass('btn-outline-success').prop('disabled', true);
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'An error occurred. Please try again.';
                        if (xhr.status === 400) {
                            let errors = xhr.responseJSON.error;
                            alert(errors)
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            alert(xhr.responseJSON.message);
                        }
                    }
                });
            });

            $('#verifyMobileBtn').on('click', function() {
                let email = $('#cafo_email').val();
                let mobileNumber = $('#cafo_mobile').val();
                let cfo_name = $('#cafo_name').val();
                let $thisButton = $(this);
                const mobileRegex = /^\d{10}$/;
                if (!mobileRegex.test(mobileNumber)) {
                    alert('Please enter a valid 10-digit mobile number.');
                    $('#cafo_mobile').focus();
                    return;
                }
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: 'mobile_verification.php',
                    method: 'POST',
                    data: {
                        mobile: mobileNumber,
                        action:'mobile_verification_email_send',
                        email: email,
                        full_name: cfo_name ? cfo_name : 'User'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#cafo_mobile').prop('readonly', true);
                            $('#div_mobile_verifcation_code').removeClass('d-none');
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'An error occurred. Please try again.';
                        if (xhr.status === 400) {
                            let errors = xhr.responseJSON.error;
                            alert(errors)
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            alert(xhr.responseJSON.message);
                        }
                    }
                });
            });

            $('#mobile_verifcation_button').on('click', function() {
                let code = $('#mobile_verifcation_code').val();
                let mobile = $('#cafo_mobile').val();
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: 'mobile_verification.php',
                    method: 'POST',
                    data: {
                        action:'verify_mobile',
                        code: code,
                        mobile: mobile
                    },
                    success: function(response) {
                        if (response.success) {
                            moblieVerification = true;
                            checkVerification();
                            if (emailVerification) {
                                $('.register-button').prop('disabled', false);
                            }
                            $('#div_mobile_verifcation_code').addClass('d-none');
                            // $('#cafo_email').prop('disabled', false);
                            $('#verifyMobileBtn').removeClass('btn-outline-secondary').text('Verified').addClass('btn-outline-success').prop('disabled', true);
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'An error occurred. Please try again.';
                        if (xhr.status === 400) {
                            let errors = xhr.responseJSON.error;
                            alert(errors)
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            alert(xhr.responseJSON.message);
                        }
                    }
                });
            });
        });
    </script>
</body>

</html>