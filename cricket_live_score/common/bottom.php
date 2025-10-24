    </main>
    
    <?php if (!$is_admin && $current_page != 'login.php' && $current_page != 'signup.php'): ?>
    <!-- Fixed Bottom Navigation (Only for User Panel) -->
    <nav class="fixed bottom-0 left-0 right-0 bg-dark-card border-t border-dark-border z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-around h-16">
                
                <!-- Home -->
                <a href="<?php echo $base_path; ?>index.php" class="flex flex-col items-center space-y-1 <?php echo ($current_page == 'index.php') ? 'text-blue-500' : 'text-gray-400 hover:text-white'; ?> transition">
                    <i class="fas fa-home text-xl"></i>
                    <span class="text-xs">Home</span>
                </a>
                
                <!-- Tournaments -->
                <a href="<?php echo $base_path; ?>index.php#tournaments" class="flex flex-col items-center space-y-1 text-gray-400 hover:text-white transition">
                    <i class="fas fa-trophy text-xl"></i>
                    <span class="text-xs">Tournaments</span>
                </a>
                
                <!-- Live Score -->
                <?php if ($is_user): ?>
                <a href="<?php echo $base_path; ?>index.php#live" class="flex flex-col items-center space-y-1 text-gray-400 hover:text-white transition">
                    <i class="fas fa-circle-dot text-xl"></i>
                    <span class="text-xs">Live</span>
                </a>
                <?php endif; ?>
                
                <!-- Profile -->
                <?php if ($is_user): ?>
                <a href="<?php echo $base_path; ?>logout.php" class="flex flex-col items-center space-y-1 text-gray-400 hover:text-white transition">
                    <i class="fas fa-user text-xl"></i>
                    <span class="text-xs">Profile</span>
                </a>
                <?php else: ?>
                <a href="<?php echo $base_path; ?>login.php" class="flex flex-col items-center space-y-1 text-gray-400 hover:text-white transition">
                    <i class="fas fa-sign-in-alt text-xl"></i>
                    <span class="text-xs">Login</span>
                </a>
                <?php endif; ?>
                
            </div>
        </div>
    </nav>
    <?php endif; ?>
    
    <!-- JavaScript -->
    <script src="<?php echo $base_path; ?>assets/js/script.js"></script>
    
</body>
</html>
