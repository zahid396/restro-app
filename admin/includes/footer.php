    </main>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.umd.min.js"></script>
    <script>
        const CSRF_TOKEN = '<?php echo $csrfToken; ?>';
        let lastOrderCount = 0;
        let notificationEnabled = true;
        
        async function checkNewOrders() {
            try {
                const response = await fetch('api/pending-orders.php');
                const data = await response.json();
                
                const badge = document.getElementById('pendingOrderCount');
                if (badge) {
                    badge.textContent = data.count;
                    badge.style.display = data.count > 0 ? 'block' : 'none';
                }
                
                if (data.count > lastOrderCount && lastOrderCount > 0 && notificationEnabled) {
                    showNewOrderAlert();
                    playNotificationSound();
                }
                lastOrderCount = data.count;
                
                if (typeof updatePendingOrdersList === 'function') {
                    updatePendingOrdersList(data.orders);
                }
            } catch (error) {
                console.error('Error checking orders:', error);
            }
        }
        
        function showNewOrderAlert() {
            const alert = document.getElementById('newOrderAlert');
            alert.style.display = 'block';
            setTimeout(() => { alert.style.display = 'none'; }, 5000);
        }
        
        function playNotificationSound() {
            const sound = document.getElementById('notificationSound');
            if (sound) {
                sound.currentTime = 0;
                sound.play().catch(e => console.log('Audio play failed:', e));
            }
        }
        
        setInterval(checkNewOrders, 5000);
        checkNewOrders();
    </script>
    <?php if (isset($pageScripts)) echo $pageScripts; ?>
</body>
</html>
