
        <!-- End of Page Content -->
        </div>


        <!-- Website Footer -->
        <footer class="footer">

            RAILCONTROL · Integrated Railway Operations Management

            <span>
                Academic project prototype
            </span>

        </footer>

    <!-- End of Main Content Area -->
    </main>

<!-- End of Application Shell -->
</div>


<!-- Main JavaScript File -->
<script src="<?= $rootPrefix ?>assets/js/app.js"></script>
<?php foreach (($extraScripts ?? []) as $src): ?>
<script src="<?= $rootPrefix . e($src) ?>"></script>
<?php endforeach; ?>

</body>
</html>
