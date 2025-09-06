<?php
/**
 * Generic Entity Table View
 * This file provides a flexible table structure that can be used for:
 * - Users (users.php, pending_users.php)
 * - Events (eventsTBL.php)
 * - Any other entity type
 */

// Ensure required variables are set
if (!isset($tableConfig)) {
    $tableConfig = [
        'title' => 'Entity Management',
        'entityType' => 'entity',
        'showCheckboxes' => false,
        'showSearch' => true,
        'showFilters' => true,
        'showBulkActions' => false,
        'columns' => [],
        'actions' => ['view'],
        'searchPlaceholder' => 'Search...',
        'emptyMessage' => 'No items found.',
        'paginationUrl' => '?page=entity&page_num=',
        'addButton' => false,
        'addButtonText' => 'Add New',
        'addButtonModal' => '#addEntityModal'
    ];
}

// Ensure required data variables are set
if (!isset($entities)) {
    $entities = [];
}
if (!isset($totalCount)) {
    $totalCount = 0;
}
if (!isset($limit)) {
    $limit = 10;
}
if (!isset($page)) {
    $page = 1;
}
if (!isset($offset)) {
    $offset = 0;
}

// Helper function to get badge class
function getBadgeClass($value, $type = 'default') {
    $badgeMap = [
        'role' => [
            'student' => 'badge-info',
            'teacher' => 'badge-warning', 
            'sslg' => 'badge-success',
            'admin' => 'badge-danger'
        ],
        'status' => [
            'active' => 'badge-success',
            'pending' => 'badge-warning',
            'inactive' => 'badge-danger',
            'approved' => 'badge-success'
        ],
        'verification' => [
            'verified' => 'badge-success',
            'unverified' => 'badge-danger'
        ],
        'event_status' => [
            'upcoming' => 'badge-info',
            'ongoing' => 'badge-warning',
            'finished' => 'badge-secondary'
        ],
        'event_type' => [
            'open' => 'badge-success',
            'exclusive' => 'badge-danger'
        ]
    ];
    
    if (isset($badgeMap[$type][strtolower($value)])) {
        return $badgeMap[$type][strtolower($value)];
    }
    
    return 'badge-secondary';
}

// Helper function to format cell value
function formatCellValue($value, $format = 'text', $options = []) {
    switch ($format) {
        case 'badge':
            $badgeType = $options['badgeType'] ?? 'default';
            $badgeClass = getBadgeClass($value, $badgeType);
            return "<span class='badge {$badgeClass}'>" . ucfirst($value) . "</span>";
            
        case 'date':
            return date('M d, Y', strtotime($value));
            
        case 'datetime':
            return date('M d, Y H:i', strtotime($value));
            
        case 'profile':
            if ($value) {
                return "<img src='{$value}' class='rounded-circle' width='35' height='35' alt='Profile' style='object-fit: cover;'>";
            }
            return "<i class='bx bx-user-circle bx-md'></i>";
            
        case 'name_with_id':
            $id = $options['id'] ?? '';
            return "<div><strong>{$value}</strong></div><small class='text-muted'>ID: {$id}</small>";
            
        case 'truncate':
            $maxLength = $options['maxLength'] ?? 50;
            if (strlen($value) > $maxLength) {
                return substr($value, 0, $maxLength) . '...';
            }
            return $value;
            
        default:
            return htmlspecialchars($value);
    }
}
?>

<div class="card mt-4 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><?php echo htmlspecialchars($tableConfig['title']); ?></h5>
        <div class="d-flex align-items-center gap-2">
            <?php if ($tableConfig['showCheckboxes']): ?>
                <span class="badge badge-warning badge-pill"><?php echo $totalCount; ?> <?php echo ucfirst($tableConfig['entityType']); ?>s</span>
            <?php else: ?>
                <span class="badge badge-info badge-pill"><?php echo $totalCount; ?> Total</span>
            <?php endif; ?>
            
            <?php if ($tableConfig['addButton']): ?>
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="<?php echo $tableConfig['addButtonModal']; ?>">
                    <i class="bx bx-plus"></i> <?php echo htmlspecialchars($tableConfig['addButtonText']); ?>
                </button>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Search and Filter Section -->
        <?php if ($tableConfig['showSearch'] || $tableConfig['showFilters']): ?>
        <div class="row mb-3">
            <?php if ($tableConfig['showSearch']): ?>
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" id="<?php echo $tableConfig['entityType']; ?>Search" 
                           placeholder="<?php echo htmlspecialchars($tableConfig['searchPlaceholder']); ?>">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="bx bx-search"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($tableConfig['showFilters'] && !empty($tableConfig['filters'])): ?>
                <?php foreach ($tableConfig['filters'] as $filter): ?>
                <div class="col-md-<?php echo $filter['width'] ?? 3; ?>">
                    <select class="form-control" id="<?php echo $filter['id']; ?>">
                        <option value=""><?php echo htmlspecialchars($filter['placeholder']); ?></option>
                        <?php foreach ($filter['options'] as $value => $label): ?>
                            <option value="<?php echo $value; ?>"><?php echo htmlspecialchars($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php if ($tableConfig['showBulkActions']): ?>
            <div class="col-md-3">
                <button type="button" class="btn btn-success btn-sm" id="bulkActionBtn" disabled>
                    <i class="bx bx-check-double"></i> Bulk Action
                </button>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Results Summary -->
        <p class="mb-2 text-muted">
            Showing <?php echo min($offset + 1, $totalCount); ?> to
            <?php echo min($offset + $limit, $totalCount); ?> of <?php echo $totalCount; ?> results
        </p>

        <!-- Entity Table -->
        <div class="table-responsive">
            <table class="table table-hover" id="<?php echo $tableConfig['entityType']; ?>Table">
                <thead class="thead-light">
                    <tr>
                        <?php if ($tableConfig['showCheckboxes']): ?>
                        <th width="5%">
                            <input type="checkbox" id="selectAll" <?php echo $totalCount == 0 ? 'disabled' : ''; ?>>
                        </th>
                        <?php endif; ?>
                        
                        <?php foreach ($tableConfig['columns'] as $column): ?>
                        <th <?php echo isset($column['width']) ? "width='{$column['width']}'" : ''; ?>>
                            <?php echo htmlspecialchars($column['title']); ?>
                        </th>
                        <?php endforeach; ?>
                        
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($entities && mysqli_num_rows($entities) > 0):
                        while ($row = mysqli_fetch_assoc($entities)):
                    ?>
                        <tr data-entity-id="<?php echo $row['id'] ?? $row['user_id'] ?? $row['event_id'] ?? ''; ?>">
                            <?php if ($tableConfig['showCheckboxes']): ?>
                            <td>
                                <input type="checkbox" class="entity-checkbox" value="<?php echo $row['id'] ?? $row['user_id'] ?? $row['event_id'] ?? ''; ?>">
                            </td>
                            <?php endif; ?>
                            
                            <?php foreach ($tableConfig['columns'] as $column): ?>
                            <td>
                                <?php 
                                $value = $row[$column['field']] ?? '';
                                $format = $column['format'] ?? 'text';
                                $options = $column['options'] ?? [];
                                
                                if ($format === 'name_with_id') {
                                    $options['id'] = $row['id'] ?? $row['user_id'] ?? $row['event_id'] ?? '';
                                }
                                
                                echo formatCellValue($value, $format, $options);
                                ?>
                            </td>
                            <?php endforeach; ?>
                            
                            <td>
                                <div class="btn-group" role="group">
                                    <?php foreach ($tableConfig['actions'] as $action): ?>
                                        <?php
                                        $actionConfig = $tableConfig['actionConfigs'][$action] ?? [];
                                        $btnClass = $actionConfig['class'] ?? 'btn-secondary';
                                        $btnIcon = $actionConfig['icon'] ?? 'bx-circle';
                                        $btnTitle = $actionConfig['title'] ?? ucfirst($action);
                                        $btnData = $actionConfig['data'] ?? [];
                                        $btnDataStr = '';
                                        foreach ($btnData as $key => $val) {
                                            $btnDataStr .= " data-{$key}='{$row[$val]}'";
                                        }
                                        ?>
                                        <button class="btn <?php echo $btnClass; ?> btn-sm <?php echo $action; ?>-btn" 
                                                title="<?php echo htmlspecialchars($btnTitle); ?>"
                                                <?php echo $btnDataStr; ?>>
                                            <i class="bx <?php echo $btnIcon; ?>"></i>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                        </tr>
                    <?php
                        endwhile;
                    else:
                        $colspan = count($tableConfig['columns']) + ($tableConfig['showCheckboxes'] ? 1 : 0) + 1;
                    ?>
                        <tr>
                            <td colspan="<?php echo $colspan; ?>" class="text-center text-muted">
                                <div class="py-4">
                                    <i class="bx bx-<?php echo $tableConfig['emptyIcon'] ?? 'x'; ?>" style="font-size: 2rem; color: #6c757d;"></i>
                                    <p class="mt-2 mb-0"><?php echo htmlspecialchars($tableConfig['emptyMessage']); ?></p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php
        $totalPages = ceil($totalCount / $limit);
        if ($totalPages > 1):
        ?>
            <nav aria-label="<?php echo ucfirst($tableConfig['entityType']); ?> table pagination">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo ($page <= 1 ? 'disabled' : ''); ?>">
                        <a class="page-link" href="<?php echo $tableConfig['paginationUrl']; ?><?php echo max($page-1,1); ?>">&lt;</a>
                    </li>
                    <?php for($i=1;$i<=$totalPages;$i++): ?>
                        <li class="page-item <?php echo ($i==$page?'active':''); ?>">
                            <a class="page-link" href="<?php echo $tableConfig['paginationUrl']; ?><?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo ($page >= $totalPages ? 'disabled' : ''); ?>">
                        <a class="page-link" href="<?php echo $tableConfig['paginationUrl']; ?><?php echo min($page+1,$totalPages); ?>">&gt;</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>
