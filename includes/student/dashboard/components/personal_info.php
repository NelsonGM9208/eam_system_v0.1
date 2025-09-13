<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}
?>

<!-- Personal Information -->
<div class="row mb-4" id="personal-info-section">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-gradient-primary text-white">
                <h5 class="mb-0">
                    <i class="bx bx-user mr-2"></i>
                    Personal Information
                </h5>
            </div>
            <div class="card-body p-4">
                <!-- Profile Header with Avatar -->
                <div class="row mb-4">
                    <div class="col-12 text-center">
                        <div class="profile-avatar mb-3">
                            <div class="avatar-circle">
                                <i class="bx bx-user"></i>
                            </div>
                        </div>
                        <h4 class="mb-1 text-primary">
                            <?php echo htmlspecialchars($personal_info['firstname'] . ' ' . $personal_info['lastname']); ?>
                        </h4>
                        <p class="text-muted mb-0">
                            <i class="bx bx-envelope mr-1"></i>
                            <?php echo htmlspecialchars($personal_info['email']); ?>
                        </p>
                        <span class="badge badge-success mt-2">
                            <i class="bx bx-check-circle me-1"></i>
                            <?php echo htmlspecialchars($personal_info['status']); ?>
                        </span>
                    </div>
                </div>

                <!-- Personal Details Grid -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-item mb-4">
                            <div class="info-label">
                                <i class="bx bx-id-card text-primary me-2"></i>
                                <strong>Full Name</strong>
                            </div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($personal_info['firstname'] . ' ' . $personal_info['lastname']); ?>
                            </div>
                        </div>
                        
                        <div class="info-item mb-4">
                            <div class="info-label">
                                <i class="bx bx-envelope text-primary me-2"></i>
                                <strong>Email Address</strong>
                            </div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($personal_info['email']); ?>
                            </div>
                        </div>
                        
                        <div class="info-item mb-4">
                            <div class="info-label">
                                <i class="bx bx-user-circle text-primary me-2"></i>
                                <strong>Gender</strong>
                            </div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($personal_info['gender']); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="info-item mb-4">
                            <div class="info-label">
                                <i class="bx bx-book text-primary me-2"></i>
                                <strong>LRN (Learner Reference Number)</strong>
                            </div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($personal_info['lrn'] ?? 'Not assigned'); ?>
                            </div>
                        </div>
                        
                        <div class="info-item mb-4">
                            <div class="info-label">
                                <i class="bx bx-barcode text-primary me-2"></i>
                                <strong>Student ID (MIS ID)</strong>
                            </div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($personal_info['mis_id'] ?? 'Not assigned'); ?>
                            </div>
                        </div>
                        
                        <div class="info-item mb-4">
                            <div class="info-label">
                                <i class="bx bx-shield-check text-primary me-2"></i>
                                <strong>Account Status</strong>
                            </div>
                            <div class="info-value">
                                <span class="badge badge-success">
                                    <i class="bx bx-check-circle me-1"></i>
                                    <?php echo htmlspecialchars($personal_info['status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Academic Information -->
                <div class="row">
                    <div class="col-12">
                        <div class="academic-section">
                            <div class="section-header">
                                <i class="bx bx-graduation text-primary me-2"></i>
                                <strong>Academic Information</strong>
                            </div>
                            <div class="academic-content">
                                <?php if ($student_stats['current_section']): ?>
                                    <div class="academic-item">
                                        <div class="academic-label">Current Section</div>
                                        <div class="academic-value">
                                            <span class="section-badge">
                                                Grade <?php echo htmlspecialchars($student_stats['section_grade']); ?> - 
                                                <?php echo htmlspecialchars($student_stats['section_name']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <?php if ($student_stats['teacher_name']): ?>
                                    <div class="academic-item">
                                        <div class="academic-label">Class Teacher</div>
                                        <div class="academic-value">
                                            <i class="bx bx-user me-1"></i>
                                            <?php echo htmlspecialchars($student_stats['teacher_name']); ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="academic-item">
                                        <div class="academic-value text-muted">
                                            <i class="bx bx-info-circle me-1"></i>
                                            Not enrolled in any section
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>