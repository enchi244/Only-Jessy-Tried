</div>
                </div>
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span><b>Copyright &copy; </b>WMSU RESEARCH <b>Version</b> 0.1</span>
                    </div>
                </div>
            </footer>
            </div>
        </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <style>
.pink{
    background-color: #f23e5d;
}
.pink:hover{
    background-color: #e32747;
}
</style>


    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">If you are ready to exit RMS Portal, select "Logout" below.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-danger pink" href="<?php echo $object->base_url; ?>logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo $object->base_url; ?>vendor/jquery/jquery.min.js"></script>
    <script src="<?php echo $object->base_url; ?>vendor/bootstrap/js/bootstrap.bundle.min.js"></script>


    <script src="<?php echo $object->base_url; ?>vendor/jquery-easing/jquery.easing.min.js"></script>

    <script src="<?php echo $object->base_url; ?>js/sb-admin-2.min.js"></script>

    <script src="<?php echo $object->base_url; ?>vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?php echo $object->base_url; ?>vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <script type="text/javascript" src="<?php echo $object->base_url; ?>vendor/parsley/dist/parsley.min.js"></script>

    <script type="text/javascript" src="<?php echo $object->base_url; ?>vendor/bootstrap-select/bootstrap-select.min.js"></script>
    
<script>
    $(document).ready(function() {
        var $sidebar = $("#accordionSidebar");
        var $body = $("body");

        // When the mouse enters anywhere on the sidebar area -> Expand it
        $sidebar.mouseenter(function() {
            if ($sidebar.hasClass("toggled")) {
                $body.removeClass("sidebar-toggled");
                $sidebar.removeClass("toggled");
            }
        });

        // When the mouse leaves the sidebar area -> Collapse it
        $sidebar.mouseleave(function() {
            // Check if the screen is large enough (we don't want this running on mobile phones)
            if ($(window).width() > 768) {
                if (!$sidebar.hasClass("toggled")) {
                    $body.addClass("sidebar-toggled");
                    $sidebar.addClass("toggled");
                    
                    // Hide any open sub-menus so they don't glitch when the sidebar shrinks
                    $('.sidebar .collapse').collapse('hide');
                }
            }
        });
    });
</script>
</body>

</html>