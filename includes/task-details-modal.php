<?php
// Task details modal for viewing full task information
?>

<!-- Task Details Modal -->
<div class="modal fade" id="taskDetailsModal" tabindex="-1" aria-labelledby="taskDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskDetailsModalLabel">Task Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="task-details-content">
                    <h4 id="detail-task-name" class="mb-4"></h4>
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Description</h6>
                        <div id="detail-task-description" class="p-3 bg-light rounded text-break">
                            <p class="mb-0 task-description-text"></p>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Start Date</h6>
                            <p id="detail-start-date" class="mb-0"></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Due Date</h6>
                            <p id="detail-due-date" class="mb-0"></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Priority</h6>
                            <p id="detail-priority" class="mb-0"><span class="badge"></span></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Project</h6>
                            <p id="detail-project" class="mb-0"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-edit btn-warning btn-sm" id="detail-edit-btn">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <a href="#" class="btn btn-success btn-sm me-2" id="detail-complete-btn">
                    <i class="fas fa-check"></i> Mark Complete
                </a>
            </div>
        </div>
    </div>
</div>
