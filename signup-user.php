<?php require_once "controllerUserData.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Signup Form</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/auth_styles.css">
    <link rel="stylesheet" href="assets/css/includes.css">
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-4 offset-md-4 form">
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
                        <select class="form-control" id="role" name="role" required>
                            <option value="" disabled <?php echo empty($role) ? 'selected' : ''; ?>>Select Role</option>
                            <option value="student" <?php echo $role === 'student' ? 'selected' : ''; ?>>Student</option>
                            <option value="teacher" <?php echo $role === 'teacher' ? 'selected' : ''; ?>>Teacher</option>
                            <option value="sslg" <?php echo $role === 'sslg' ? 'selected' : ''; ?>>SSLG</option>
                        </select>
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
</body>
</html>