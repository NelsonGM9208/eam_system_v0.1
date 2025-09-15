<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}
require_once __DIR__ . "/../../utils/index.php";
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="bx bx-file"></i> Excuse Letters
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle"></i>
                        <strong>Excuse Letter Management</strong> - This feature is coming soon!
                    </div>
                    <p>Here you will be able to review and manage student excuse letters, approve or reject submissions, and track excuse letter history.</p>
                    
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="card border-warning">
                                <div class="card-body text-center">
                                    <i class="bx bx-time text-warning" style="font-size: 3rem;"></i>
                                    <h5 class="card-title mt-3">Pending Review</h5>
                                    <p class="card-text">Excuse letters awaiting your review</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <i class="bx bx-check-circle text-success" style="font-size: 3rem;"></i>
                                    <h5 class="card-title mt-3">Approved</h5>
                                    <p class="card-text">Excuse letters you have approved</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-danger">
                                <div class="card-body text-center">
                                    <i class="bx bx-x-circle text-danger" style="font-size: 3rem;"></i>
                                    <h5 class="card-title mt-3">Rejected</h5>
                                    <p class="card-text">Excuse letters you have rejected</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bx bx-file-blank text-primary"></i>
                                        Document Review
                                    </h5>
                                    <p class="card-text">Review uploaded documents and supporting materials</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bx bx-comment text-info"></i>
                                        Add Comments
                                    </h5>
                                    <p class="card-text">Add feedback and comments to excuse letters</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
