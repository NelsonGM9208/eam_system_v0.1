<?php
/**
 * Universal Modal System
 * This file provides a flexible modal structure that can be used for:
 * - View details
 * - Edit forms
 * - Add forms
 * - Delete confirmations
 * - Any custom modal content
 */

// Ensure required variables are set
if (!isset($modalConfig)) {
    $modalConfig = [
        'id' => 'universalModal',
        'title' => 'Modal Title',
        'size' => 'modal-lg', // modal-sm, modal-lg, modal-xl
        'type' => 'view', // view, edit, add, delete, custom
        'showHeader' => true,
        'showFooter' => true,
        'closeButtonText' => 'Close',
        'primaryButtonText' => 'Save',
        'primaryButtonClass' => 'btn-primary',
        'showCloseButton' => true,
        'showPrimaryButton' => false,
        'formId' => null,
        'formAction' => null,
        'formMethod' => 'POST',
        'dataAttributes' => [],
        'customHeader' => null,
        'customFooter' => null
    ];
}

// Helper function to build data attributes string
function buildDataAttributes($attributes) {
    if (empty($attributes)) return '';
    
    $attrString = '';
    foreach ($attributes as $key => $value) {
        $attrString .= " data-{$key}=\"{$value}\"";
    }
    return $attrString;
}

// Helper function to get modal size class
function getModalSizeClass($size) {
    $validSizes = ['modal-sm', 'modal-lg', 'modal-xl'];
    return in_array($size, $validSizes) ? $size : 'modal-lg';
}

// Helper function to get icon based on modal type
function getModalIcon($type) {
    switch ($type) {
        case 'view': return 'bx-show';
        case 'edit': return 'bx-edit';
        case 'add': return 'bx-plus';
        case 'delete': return 'bx-trash';
        default: return 'bx-info-circle';
    }
}
?>

<div class="modal fade" id="<?php echo htmlspecialchars($modalConfig['id']); ?>" tabindex="-1" role="dialog" aria-labelledby="<?php echo htmlspecialchars($modalConfig['id']); ?>Label" aria-hidden="true">
    <div class="modal-dialog <?php echo getModalSizeClass($modalConfig['size']); ?>" role="document">
        <div class="modal-content">
            
            <!-- Modal Header -->
            <?php if ($modalConfig['showHeader']): ?>
                <div class="modal-header">
                    <?php if ($modalConfig['customHeader']): ?>
                        <?php echo $modalConfig['customHeader']; ?>
                    <?php else: ?>
                        <h5 class="modal-title" id="<?php echo htmlspecialchars($modalConfig['id']); ?>Label">
                            <i class="bx <?php echo getModalIcon($modalConfig['type']); ?> me-2"></i>
                            <?php echo htmlspecialchars($modalConfig['title']); ?>
                        </h5>
                    <?php endif; ?>
                    
                    <?php if ($modalConfig['showCloseButton']): ?>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- Modal Body -->
            <div class="modal-body" id="<?php echo htmlspecialchars($modalConfig['id']); ?>Content">
                <!-- Content will be loaded here dynamically -->
                <?php if (isset($modalContent)): ?>
                    <?php echo $modalContent; ?>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="bx bx-loader-alt bx-spin" style="font-size: 2rem;"></i>
                        <p class="mt-2">Loading content...</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Modal Footer -->
            <?php if ($modalConfig['showFooter']): ?>
                <div class="modal-footer">
                    <?php if ($modalConfig['customFooter']): ?>
                        <?php echo $modalConfig['customFooter']; ?>
                    <?php else: ?>
                        <?php if ($modalConfig['showCloseButton']): ?>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                <?php echo htmlspecialchars($modalConfig['closeButtonText']); ?>
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($modalConfig['showPrimaryButton']): ?>
                            <button type="submit" 
                                    class="btn <?php echo htmlspecialchars($modalConfig['primaryButtonClass']); ?>"
                                    <?php if ($modalConfig['formId']): ?>form="<?php echo htmlspecialchars($modalConfig['formId']); ?>"<?php endif; ?>>
                                <?php echo htmlspecialchars($modalConfig['primaryButtonText']); ?>
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
        </div>
    </div>
</div>

<!-- JavaScript for Modal Functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize modal functionality
    const modalId = '<?php echo htmlspecialchars($modalConfig['id']); ?>';
    const modal = document.getElementById(modalId);
    
    if (modal) {
        // Handle modal show event
        $(modal).on('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const modalInstance = this;
            
            // Extract data attributes
            <?php if (!empty($modalConfig['dataAttributes'])): ?>
            const dataAttributes = <?php echo json_encode($modalConfig['dataAttributes']); ?>;
            dataAttributes.forEach(attr => {
                if (button.dataset[attr]) {
                    modalInstance.dataset[attr] = button.dataset[attr];
                }
            });
            <?php endif; ?>
            
            // Handle different modal types
            const modalType = '<?php echo htmlspecialchars($modalConfig['type']); ?>';
            if (modalType === 'view' && button.dataset.id) {
                loadModalContent(modalInstance, modalType, button.dataset.id, button.dataset.entity);
            }
        });
        
        // Handle modal hidden event
        $(modal).on('hidden.bs.modal', function () {
            // Clear modal content when hidden
            const contentDiv = this.querySelector('.modal-body');
            if (contentDiv) {
                contentDiv.innerHTML = '<div class="text-center text-muted py-4"><i class="bx bx-loader-alt bx-spin" style="font-size: 2rem;"></i><p class="mt-2">Loading content...</p></div>';
            }
        });
    }
});

// Function to load modal content based on type and ID
function loadModalContent(modal, type, id, entity = null) {
    const contentDiv = modal.querySelector('.modal-body');
    if (!contentDiv) return;
    
    let url = '';
    switch (type) {
        case 'view':
            if (entity === 'user') {
                url = `modals/view_user.php?id=${id}`;
            } else if (entity === 'event') {
                url = `modals/view_event.php?id=${id}`;
            } else {
                url = `modals/user_details.php?id=${id}`;
            }
            break;
        case 'edit':
            if (entity === 'user') {
                url = `modals/edit_user.php?id=${id}`;
            } else if (entity === 'event') {
                url = `modals/edit_event.php?id=${id}`;
            }
            break;
        default:
            url = `modals/user_details.php?id=${id}`;
    }
    
    if (url) {
        // Show loading state
        contentDiv.innerHTML = '<div class="text-center text-muted py-4"><i class="bx bx-loader-alt bx-spin" style="font-size: 2rem;"></i><p class="mt-2">Loading...</p></div>';
        
        // Load content via AJAX
        fetch(url)
            .then(response => response.text())
            .then(html => {
                contentDiv.innerHTML = html;
            })
            .catch(error => {
                contentDiv.innerHTML = '<div class="text-center text-danger py-4"><i class="bx bx-error-circle" style="font-size: 2rem;"></i><p class="mt-2">Error loading content</p></div>';
                console.error('Error loading modal content:', error);
            });
    }
}
</script>
