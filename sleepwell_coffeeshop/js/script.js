// js/script.js
let cart = [];

function addToCart(item) {
    if (item.quantity < 1 || item.quantity > parseInt(document.getElementById(`quantity-${item.id}`).max)) {
        alert('Invalid quantity selected.');
        return;
    }
    const existingItem = cart.find(cartItem => cartItem.id === item.id);
    if (existingItem) {
        existingItem.quantity += item.quantity;
    } else {
        cart.push(item);
    }
    updateCart();
    const notification = document.createElement('div');
    notification.className = 'notification success';
    notification.textContent = `Added ${item.quantity} x ${item.name} to cart!`;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 5000);
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCart();
}

function updateCart() {
    const cartItems = document.getElementById('cart-items');
    const cartTotal = document.getElementById('cart-total');
    const cartData = document.getElementById('cart-data');
    cartItems.innerHTML = '';
    let total = 0;
    
    cart.forEach((item, index) => {
        const itemTotal = item.price * item.quantity;
        total += itemTotal;
        const div = document.createElement('div');
        div.className = 'cart-item';
        div.innerHTML = `
            <span>${item.name} - $${item.price.toFixed(2)} x ${item.quantity} = $${itemTotal.toFixed(2)}</span>
            <button onclick="removeFromCart(${index})">Remove</button>
        `;
        cartItems.appendChild(div);
    });
    
    cartTotal.textContent = total.toFixed(2);
    cartData.value = JSON.stringify(cart);
}