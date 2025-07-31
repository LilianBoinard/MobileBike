document.addEventListener('DOMContentLoaded', function () {
    // Éléments du DOM
    const quantityInputs = document.querySelectorAll('.quantity-input');
    const quantityBtns = document.querySelectorAll('.quantity-btn');
    const removeBtns = document.querySelectorAll('.btn-remove');
    const checkoutBtn = document.getElementById('checkout-btn');
    const clearCartBtn = document.getElementById('clear-cart-btn');
    const modal = document.getElementById('confirmation-modal');
    const notification = document.getElementById('notification');

    // Configuration des endpoints
    const endpoints = {
        updateQuantity: '/dashboard/cart/update-quantity',
        removeItem: '/dashboard/cart/remove',
        clearCart: '/dashboard/cart/clear',
        checkout: '/dashboard/cart/checkout'
    };

    // Gestion des changements de quantité via input
    quantityInputs.forEach(input => {
        let timeoutId;

        input.addEventListener('input', function () {
            clearTimeout(timeoutId);
            const productId = this.dataset.productId;
            const quantity = parseInt(this.value);

            // Délai pour éviter trop de requêtes
            timeoutId = setTimeout(() => {
                if (quantity > 0) {
                    updateQuantity(productId, quantity);
                }
            }, 500);
        });

        // Validation en temps réel
        input.addEventListener('blur', function () {
            const quantity = parseInt(this.value);
            const max = parseInt(this.getAttribute('max'));

            if (quantity < 1) {
                this.value = 1;
                updateQuantity(this.dataset.productId, 1);
            } else if (quantity > max) {
                this.value = max;
                updateQuantity(this.dataset.productId, max);
                showNotification('Stock insuffisant', 'warning');
            }
        });
    });

    // Gestion des boutons +/-
    quantityBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const productId = this.dataset.productId;
            const input = document.querySelector(`.quantity-input[data-product-id="${productId}"]`);
            const currentQuantity = parseInt(input.value);
            const maxQuantity = parseInt(input.getAttribute('max'));

            let newQuantity = currentQuantity;

            if (this.classList.contains('quantity-increase')) {
                if (currentQuantity < maxQuantity) {
                    newQuantity = currentQuantity + 1;
                } else {
                    showNotification('Stock insuffisant', 'warning');
                    return;
                }
            } else if (this.classList.contains('quantity-decrease')) {
                if (currentQuantity > 1) {
                    newQuantity = currentQuantity - 1;
                } else {
                    return;
                }
            }

            input.value = newQuantity;
            updateQuantity(productId, newQuantity);
            updateQuantityButtons(productId, newQuantity, maxQuantity);
        });
    });

    // Gestion de la suppression d'articles
    removeBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const productId = this.dataset.productId;
            const itemElement = this.closest('.cart-item');
            const productName = itemElement.querySelector('.item-name').textContent;

            console.log(productId)

            showConfirmationModal(
                'Supprimer l\'article',
                `Êtes-vous sûr de vouloir supprimer "${productName}" du panier ?`,
                () => removeItem(productId)
            );
        });
    });

    // Gestion du bouton vider le panier
    if (clearCartBtn) {
        clearCartBtn.addEventListener('click', function () {
            showConfirmationModal(
                'Vider le panier',
                'Êtes-vous sûr de vouloir vider complètement votre panier ?',
                clearCart
            );
        });
    }

    // Gestion du checkout
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function () {
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';

            checkout();
        });
    }

    // Fonctions AJAX
    async function updateQuantity(productId, quantity) {
        try {
            const response = await fetch(endpoints.updateQuantity, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: quantity
                })
            });

            const data = await response.json();

            if (data.success) {
                updateCartDisplay(productId, quantity);
                updateCartSummary();
                showNotification(data.message, 'success');
            } else {
                showNotification(data.message, 'error');
                // Restaurer la quantité précédente
                revertQuantityInput(productId);
            }
        } catch (error) {
            console.error('Erreur lors de la mise à jour:', error);

            showNotification('Erreur de connexion', 'error');
            revertQuantityInput(productId);
        }
    }

    async function removeItem(productId) {
        try {
            const response = await fetch(endpoints.removeItem, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    product_id: productId
                })
            });

            const data = await response.json();

            if (data.success) {
                const itemElement = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
                if (itemElement) {
                    itemElement.style.transition = 'opacity 0.3s ease';
                    itemElement.style.opacity = '0';
                    setTimeout(() => {
                        itemElement.remove();
                        updateCartSummary();
                        checkEmptyCart();
                    }, 300);
                }
                showNotification(data.message, 'success');
            } else {
                showNotification(data.message, 'error');
            }
        } catch (error) {
            console.error('Erreur lors de la suppression:', error);
            showNotification('Erreur de connexion', 'error');
        }
    }

    async function clearCart() {
        try {
            const response = await fetch(endpoints.clearCart, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                const cartItems = document.querySelectorAll('.cart-item');
                cartItems.forEach(item => {
                    item.style.transition = 'opacity 0.3s ease';
                    item.style.opacity = '0';
                });

                setTimeout(() => {
                    cartItems.forEach(item => item.remove());
                    updateCartSummary();
                    checkEmptyCart();
                }, 300);

                showNotification(data.message, 'success');
            } else {
                showNotification(data.message, 'error');
            }
        } catch (error) {
            console.error('Erreur lors du vidage du panier:', error);
            showNotification('Erreur de connexion', 'error');
        }
    }

    async function checkout() {
        try {
            const response = await fetch(endpoints.checkout, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                showNotification('Commande validée avec succès !', 'success');
                setTimeout(() => {
                    window.location.href = data.redirect_url || '/dashboard/orders';
                }, 1500);
            } else {
                showNotification(data.message, 'error');
                resetCheckoutButton();
            }
        } catch (error) {
            console.error('Erreur lors du checkout:', error);
            showNotification('Erreur de connexion', 'error');
            resetCheckoutButton();
        }
    }

// Fonctions utilitaires
    function updateCartDisplay(productId, quantity) {
        const itemElement = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
        if (itemElement) {
            const priceElement = itemElement.querySelector('.item-price');
            const unitPrice = parseFloat(priceElement.dataset.unitPrice);
            const totalPrice = unitPrice * quantity;

            priceElement.textContent = formatPrice(totalPrice);

            const input = itemElement.querySelector('.quantity-input');
            const maxQuantity = parseInt(input.getAttribute('max'));
            updateQuantityButtons(productId, quantity, maxQuantity);
        }
    }

    function updateQuantityButtons(productId, quantity, maxQuantity) {
        const decreaseBtn = document.querySelector(`.quantity-decrease[data-product-id="${productId}"]`);
        const increaseBtn = document.querySelector(`.quantity-increase[data-product-id="${productId}"]`);

        if (decreaseBtn) {
            decreaseBtn.disabled = quantity <= 1;
        }

        if (increaseBtn) {
            increaseBtn.disabled = quantity >= maxQuantity;
        }
    }

    function updateCartSummary() {
        let subtotal = 0;
        let totalItems = 0;

        document.querySelectorAll('.cart-item').forEach(item => {
            const quantity = parseInt(item.querySelector('.quantity-input').value);
            const unitPrice = parseFloat(item.querySelector('.item-price').dataset.unitPrice);

            subtotal += unitPrice * quantity;
            totalItems += quantity;
        });

        // Mise à jour du sous-total
        const subtotalElement = document.getElementById('cart-subtotal');
        if (subtotalElement) {
            subtotalElement.textContent = formatPrice(subtotal);
        }

        // Mise à jour du nombre d'articles
        const itemCountElement = document.getElementById('cart-item-count');
        if (itemCountElement) {
            itemCountElement.textContent = totalItems;
        }

        // Calcul des taxes et du total
        const taxRate = 0.20; // TVA 20%
        const tax = subtotal * taxRate;
        const total = subtotal + tax;

        const taxElement = document.getElementById('cart-tax');
        if (taxElement) {
            taxElement.textContent = formatPrice(tax);
        }

        const totalElement = document.getElementById('cart-total');
        if (totalElement) {
            totalElement.textContent = formatPrice(total);
        }

        // Mise à jour du badge du panier dans la navigation
        updateCartBadge(totalItems);
    }

    function updateCartBadge(count) {
        const badge = document.querySelector('.cart-badge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        }
    }

    function checkEmptyCart() {
        const cartItems = document.querySelectorAll('.cart-item');
        const emptyMessage = document.getElementById('empty-cart-message');
        const cartContent = document.getElementById('cart-content');

        if (cartItems.length === 0) {
            if (cartContent) cartContent.style.display = 'none';
            if (emptyMessage) emptyMessage.style.display = 'block';
            if (checkoutBtn) checkoutBtn.disabled = true;
        } else {
            if (cartContent) cartContent.style.display = 'block';
            if (emptyMessage) emptyMessage.style.display = 'none';
            if (checkoutBtn) checkoutBtn.disabled = false;
        }
    }

    function revertQuantityInput(productId) {
        const input = document.querySelector(`.quantity-input[data-product-id="${productId}"]`);
        if (input && input.dataset.previousValue) {
            input.value = input.dataset.previousValue;
        }
    }

    function resetCheckoutButton() {
        if (checkoutBtn) {
            checkoutBtn.disabled = false;
            checkoutBtn.innerHTML = '<i class="fas fa-credit-card"></i> Finaliser la commande';
        }
    }

    function formatPrice(price) {
        return new Intl.NumberFormat('fr-FR', {
            style: 'currency',
            currency: 'EUR'
        }).format(price);
    }

    function showNotification(message, type = 'info') {
        if (!notification) return;

        notification.className = `notification ${type}`;
        notification.textContent = message;
        notification.style.display = 'block';
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(-20px)';

        // Animation d'apparition
        setTimeout(() => {
            notification.style.transition = 'all 0.3s ease';
            notification.style.opacity = '1';
            notification.style.transform = 'translateY(0)';
        }, 10);

        // Masquer automatiquement après 5 secondes
        setTimeout(() => {
            hideNotification();
        }, 5000);
    }

    function hideNotification() {
        if (!notification) return;

        notification.style.opacity = '0';
        notification.style.transform = 'translateY(-20px)';

        setTimeout(() => {
            notification.style.display = 'none';
        }, 300);
    }

    function showConfirmationModal(title, message, onConfirm) {
        if (!modal) return;

        const modalTitle = modal.querySelector('.modal-title');
        const modalBody = modal.querySelector('.modal-body');
        const confirmBtn = modal.querySelector('.btn-confirm');
        const cancelBtn = modal.querySelector('.btn-cancel');

        if (modalTitle) modalTitle.textContent = title;
        if (modalBody) modalBody.textContent = message;

        modal.style.display = 'flex';
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.style.opacity = '1';
        }, 10);

        // Gestionnaires d'événements pour les boutons
        const handleConfirm = () => {
            hideModal();
            onConfirm();
            confirmBtn.removeEventListener('click', handleConfirm);
            cancelBtn.removeEventListener('click', handleCancel);
        };

        const handleCancel = () => {
            hideModal();
            confirmBtn.removeEventListener('click', handleConfirm);
            cancelBtn.removeEventListener('click', handleCancel);
        };

        if (confirmBtn) confirmBtn.addEventListener('click', handleConfirm);
        if (cancelBtn) cancelBtn.addEventListener('click', handleCancel);

        // Fermer avec Escape
        const handleEscape = (e) => {
            if (e.key === 'Escape') {
                handleCancel();
                document.removeEventListener('keydown', handleEscape);
            }
        };

        document.addEventListener('keydown', handleEscape);
    }

    function hideModal() {
        if (!modal) return;

        modal.style.opacity = '0';
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }

// Sauvegarde des valeurs précédentes pour le revert
    quantityInputs.forEach(input => {
        input.addEventListener('focus', function () {
            this.dataset.previousValue = this.value;
        });
    });

// Fermer la notification en cliquant dessus
    if (notification) {
        notification.addEventListener('click', hideNotification);
    }

// Fermer le modal en cliquant à l'extérieur
    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                hideModal();
            }
        });
    }

// Initialisation
    updateCartSummary();
    checkEmptyCart();

// Mise à jour des boutons de quantité au chargement
    quantityInputs.forEach(input => {
        const productId = input.dataset.productId;
        const quantity = parseInt(input.value);
        const maxQuantity = parseInt(input.getAttribute('max'));
        updateQuantityButtons(productId, quantity, maxQuantity);
    });
});