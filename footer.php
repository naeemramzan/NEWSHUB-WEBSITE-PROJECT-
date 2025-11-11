<footer class="footer-main pt-10 mt-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="footer-col">
                    <h4 class="mb-4 text-white font-bold uppercase">About Us</h4>
                    <p class="text-sm">NewsHub brings you 24/7 Live Streaming, Headlines, Bulletins, Talk Shows, and much more.</p>
                </div>
                <div class="footer-col">
                    <h4 class="mb-4 text-white font-bold uppercase">Corporate</h4>
                    <a href="feedback.php" class="text-sm block mb-2 hover:text-white">Feedback</a>
                    <a href="contact.php" class="text-sm block mb-2 hover:text-white">Contact Us</a>
                    <a href="about.php" class="text-sm block mb-2 hover:text-white">About Us</a>
                    <a href="terms.php" class="text-sm block mb-2 hover:text-white">Terms & Conditions</a>
                    
                    <a href="privacypolicy.php" class="text-sm block mb-2 hover:text-white"> Privacy Policy</a>
                </div>
                <div class="footer-col">
                    <h4 class="mb-4 text-white font-bold uppercase">Our Network</h4>
                    <a href="#" class="text-sm block mb-2 hover:text-white">NewsHub News</a>
                    <a href="#" class="text-sm block mb-2 hover:text-white">NewsHub Digital</a>
                    
                </div>
                <div class="footer-col">
                    <h4 class="mb-4 text-white font-bold uppercase">Download Now!</h4>
                    <a href="#" class="text-sm block mb-2 hover:text-white">App Store</a>
                    <a href="#" class="text-sm block mb-2 hover:text-white">Google Play</a>
                    <a href="#" class="text-sm block mb-2 hover:text-white">AppGallery</a>
                    
                </div>
            </div>
        </div>
        <div class="footer-bottom mt-8 py-4">
            <p class="text-center text-xs">&copy; <?php echo date('Y'); ?> NewsHub. All Rights Reserved.</p>
        </div>
    </footer>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuButton = document.getElementById('mobile-menu-button');
            const menuOverlay = document.getElementById('mobile-menu-overlay');
            const closeButton = document.getElementById('mobile-menu-close');

            function openMenu() {
                menuOverlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closeMenu() {
                menuOverlay.classList.add('hidden');
                document.body.style.overflow = '';
            }

            menuButton.addEventListener('click', openMenu);
            closeButton.addEventListener('click', closeMenu);
            
            menuOverlay.addEventListener('click', function(event) {
                if (event.target === menuOverlay) {
                    closeMenu();
                }
            });
        });
    </script>
</body>
</html>