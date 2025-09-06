<?php require_once "controllerUserData.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Signup Form</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/auth_styles.css">
    <link rel="stylesheet" href="assets/css/includes.css">
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-4 offset-md-4 col-sm-8 offset-sm-2 col-12 form">
                <form action="signup-user.php" method="POST" autocomplete="">
                    <h2 class="text-center">Signup Form</h2>
                    <p class="text-center">It's quick and easy.</p>
                    <?php
                    if(count($errors) == 1){
                        ?>
                        <div class="alert alert-danger text-center">
                            <?php
                            foreach($errors as $showerror){
                                echo $showerror;
                            }
                            ?>
                        </div>
                        <?php
                    }elseif(count($errors) > 1){
                        ?>
                        <div class="alert alert-danger">
                            <?php
                            foreach($errors as $showerror){
                                ?>
                                <li><?php echo $showerror; ?></li>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    }
                    ?>
                    <div class="form-group">
                        <input class="form-control" type="text" name="fname" placeholder="First Name" required value="<?php echo $fname ?>">
                    </div>
                    <div class="form-group">
                        <input class="form-control" type="text" name="lname" placeholder="Last Name" required value="<?php echo $lname ?>">
                    </div>
                    <div class="form-group">
                            <select class="form-control" id="gender" name="gender" required>
                                <option value="" disabled <?php echo empty($gender) ? 'selected' : ''; ?>>Select Gender</option>
                                <option value="Male" <?php echo $gender === 'Male' ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo $gender === 'Female' ? 'selected' : ''; ?>>Female</option>
                            </select>
                        </div>
                    <div class="form-group">
                        <select class="form-control" id="role" name="role" required onchange="toggleRoleFields()">
                            <option value="" disabled <?php echo empty($role) ? 'selected' : ''; ?>>Select Role</option>
                            <option value="student" <?php echo $role === 'student' ? 'selected' : ''; ?>>Student</option>
                            <option value="teacher" <?php echo $role === 'teacher' ? 'selected' : ''; ?>>Teacher</option>
                            <option value="sslg" <?php echo $role === 'sslg' ? 'selected' : ''; ?>>SSLG</option>
                        </select>
                    </div>
                    
                    <!-- Student-specific fields -->
                    <div class="form-group" id="studentFields" style="display: none;">
                        <input class="form-control" type="text" name="lrn" placeholder="LRN (12 characters)" maxlength="12" value="<?php echo $lrn ?? '' ?>">
                        <small class="form-text text-muted">Learner Reference Number - exactly 12 characters</small>
                    </div>
                    <div class="form-group" id="studentFields2" style="display: none;">
                        <input class="form-control" type="text" name="mis_id" placeholder="MIS ID (6 characters)" maxlength="6" value="<?php echo $mis_id ?? '' ?>">
                        <small class="form-text text-muted">Management Information System ID - exactly 6 characters</small>
                    </div>
                    
                    <!-- Teacher-specific fields -->
                    <div class="form-group" id="teacherFields" style="display: none;">
                        <input class="form-control" type="text" name="course" placeholder="Course/Subject" value="<?php echo $course ?? '' ?>">
                        <small class="form-text text-muted">Course or subject you will teach</small>
                    </div>
                    <div class="form-group">
                        <input class="form-control" type="email" name="email" placeholder="Email Address" required value="<?php echo $email ?>">
                    </div>
                    <div class="form-group">
                        <input class="form-control" type="password" name="password" placeholder="Password" required>
                    </div>
                    <div class="form-group">
                        <input class="form-control" type="password" name="cpassword" placeholder="Confirm password" required>
                    </div>
                    <div class="form-group">
                        <input class="form-control button" type="submit" name="signup" value="Signup">
                    </div>
                    <div class="link login-link text-center">Already a member? <a href="login.php">Login here</a></div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="mt-auto">
        <?php include "includes/header.php"; ?>
    </div>
    
    <script>
        function toggleRoleFields() {
            const role = document.getElementById('role').value;
            const studentFields = document.getElementById('studentFields');
            const studentFields2 = document.getElementById('studentFields2');
            const teacherFields = document.getElementById('teacherFields');
            
            // Hide all fields first
            studentFields.style.display = 'none';
            studentFields2.style.display = 'none';
            teacherFields.style.display = 'none';
            
            // Remove required attributes
            document.querySelector('input[name="lrn"]').removeAttribute('required');
            document.querySelector('input[name="mis_id"]').removeAttribute('required');
            document.querySelector('input[name="course"]').removeAttribute('required');
            
            // Show relevant fields based on role
            if (role === 'student') {
                studentFields.style.display = 'block';
                studentFields2.style.display = 'block';
                document.querySelector('input[name="lrn"]').setAttribute('required', 'required');
                document.querySelector('input[name="mis_id"]').setAttribute('required', 'required');
            } else if (role === 'teacher') {
                teacherFields.style.display = 'block';
                document.querySelector('input[name="course"]').setAttribute('required', 'required');
            }
        }
        
        // Initialize fields on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleRoleFields();
        });
    </script>
</body>
</html>