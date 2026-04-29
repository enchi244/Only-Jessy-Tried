<?php
require_once 'core/config.php';
include('core/rms.php'); $object = new rms();
include 'includes/header.php';
?>

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">System Settings</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <ul class="nav nav-tabs card-header-tabs" id="settingsTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="colleges-tab" data-toggle="tab" href="#colleges" role="tab" aria-controls="colleges" aria-selected="true">
                        <i class="fas fa-university fa-sm fa-fw mr-2 text-gray-400"></i>Colleges
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="disciplines-tab" data-toggle="tab" href="#disciplines" role="tab" aria-controls="disciplines" aria-selected="false">
                        <i class="fas fa-book fa-sm fa-fw mr-2 text-gray-400"></i>Disciplines
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="sdg-tab" data-toggle="tab" href="#sdg" role="tab" aria-controls="sdg" aria-selected="false">
                        <i class="fas fa-globe fa-sm fa-fw mr-2 text-gray-400"></i>Agenda SDG Tracker
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="archive-tab" data-toggle="tab" href="#archive" role="tab" aria-controls="archive" aria-selected="false">
                        <i class="fas fa-trash fa-sm fa-fw mr-2 text-gray-400"></i>Recycle Bin
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="card-body">
            <div class="tab-content" id="settingsTabsContent">
                
                <div class="tab-pane fade show active" id="colleges" role="tabpanel" aria-labelledby="colleges-tab">
                    <?php include 'modules/colleges/colleges.php'; ?>
                </div>

                <div class="tab-pane fade" id="disciplines" role="tabpanel" aria-labelledby="disciplines-tab">
                    <?php include 'modules/disciplines/disciplines.php'; ?>
                </div>

                <div class="tab-pane fade" id="sdg" role="tabpanel" aria-labelledby="sdg-tab">
                    <?php include 'modules/trackers/track_agenda_sdg.php'; ?>
                </div>

                <div class="tab-pane fade" id="archive" role="tabpanel" aria-labelledby="archive-tab">
                    <?php include 'modules/researchers/archive.php'; ?>
                </div>

            </div>
        </div>
    </div>

</div>
<?php include 'includes/footer.php'; ?>
<script>
$(document).ready(function(){
    // Force DataTables to recalculate widths when a tab is clicked/shown
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });
});
</script>