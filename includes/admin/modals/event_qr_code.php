<?php
if (!defined('IN_APP')) {
    define('IN_APP', true);
}
?>

<!-- Event QR Code Modal -->
<div class="modal fade" id="eventQRCodeModal" tabindex="-1" aria-labelledby="eventQRCodeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="eventQRCodeModalLabel">
                    <i class="bx bx-qr-scan me-2"></i>
                    Event QR Code
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="eventQRCodeContent">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Generating QR code...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="bx bx-x mr-1"></i>Close
                </button>
                <button type="button" class="btn btn-success" id="downloadPNGBtn" style="display: none;">
                    <i class="bx bx-download mr-1"></i>Download PNG
                </button>
                <button type="button" class="btn btn-info" id="downloadPDFBtn" style="display: none;">
                    <i class="bx bx-file mr-1"></i>Download PDF
                </button>
                <button type="button" class="btn btn-warning" id="printQRBtn" style="display: none;">
                    <i class="bx bx-printer mr-1"></i>Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Print Styles -->
<style>
@media print {
    .modal-dialog {
        max-width: none !important;
        margin: 0 !important;
    }
    
    .modal-content {
        border: none !important;
        box-shadow: none !important;
    }
    
    .modal-header,
    .modal-footer {
        display: none !important;
    }
    
    .modal-body {
        padding: 0 !important;
    }
    
    .qr-print-container {
        text-align: center;
        padding: 20px;
    }
    
    .qr-print-container h3 {
        margin-bottom: 20px;
        color: #000 !important;
    }
    
    .qr-print-container .qr-code {
        margin: 20px 0;
    }
    
    .qr-print-container .event-details {
        margin-top: 20px;
        text-align: left;
    }
    
    .qr-print-container .event-details h5 {
        color: #000 !important;
        margin-bottom: 10px;
    }
    
    .qr-print-container .event-details p {
        margin: 5px 0;
        color: #000 !important;
    }
}
</style>
