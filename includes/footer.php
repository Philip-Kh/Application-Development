            </div>
        </main>
        
        <!-- Footer -->
        <footer class="footer">
            <p>
                All Rights Reserved &copy; <?php echo date('Y'); ?> 
                <a href="#">Quick Mart</a> - Inventory & Order Management System
            </p>
        </footer>
    </div>
    
    <!-- Main JavaScript -->
    <?php 
    $currentDir = basename(dirname($_SERVER['PHP_SELF']));
    $jsPath = $currentDir === 'admin' ? '../../assets/js/main.js' : '../assets/js/main.js';
    ?>
    <script src="<?php echo $jsPath; ?>"></script>
</body>
</html>
