// API Helper Functions
class API {
    static async request(endpoint, options = {}) {
        const url = `/api/${endpoint}`;
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'include',
            ...options
        };

        try {
            const response = await fetch(url, defaultOptions);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error || 'Request failed');
            }
            
            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    static get(endpoint) {
        return this.request(endpoint, { method: 'GET' });
    }

    static post(endpoint, body) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(body)
        });
    }

    static put(endpoint, body) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(body)
        });
    }

    static delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    }
}

// Main App
class App {
    constructor() {
        this.cart = [];
        this.user = null;
        this.init();
    }

    async init() {
        this.setupEventListeners();
        await this.checkAuth();
    }

    setupEventListeners() {
        document.getElementById('browse-products').addEventListener('click', () => this.loadProducts());
        document.getElementById('auth-link').addEventListener('click', () => this.showAuthModal());
        document.getElementById('cart-link').addEventListener('click', () => this.showCart());
    }

    async checkAuth() {
        try {
            const user = await API.get('auth/profile');
            this.user = user;
            this.updateAuthLink();
        } catch (error) {
            console.log('User not authenticated');
        }
    }

    updateAuthLink() {
        const authLink = document.getElementById('auth-link');
        if (this.user) {
            authLink.textContent = `${this.user.name} (تسجيل خروج)`;
            authLink.onclick = () => this.logout();
        }
    }

    async loadProducts() {
        try {
            const products = await API.get('products');
            this.displayProducts(products);
            document.getElementById('products-section').style.display = 'block';
        } catch (error) {
            alert('فشل تحميل المنتجات');
        }
    }

    displayProducts(products) {
        const container = document.getElementById('products-container');
        container.innerHTML = '';

        products.forEach(product => {
            const card = document.createElement('div');
            card.className = 'product-card';
            card.innerHTML = `
                <div class="product-image">صورة المنتج</div>
                <div class="product-info">
                    <div class="product-name">${product.name}</div>
                    <div class="product-price">${product.price} ريال</div>
                    <div class="product-quantity">المتوفر: ${product.quantity}</div>
                    <button class="btn-add-cart" onclick="app.addToCart(${product.id}, '${product.name}', ${product.price})">أضف إلى السلة</button>
                </div>
            `;
            container.appendChild(card);
        });
    }

    async addToCart(productId, productName, price) {
        if (!this.user) {
            alert('يرجى تسجيل الدخول أولاً');
            this.showAuthModal();
            return;
        }

        try {
            await API.post('cart/add', {
                product_id: productId,
                quantity: 1
            });
            alert('تم إضافة المنتج إلى السلة');
            this.updateCartCount();
        } catch (error) {
            alert('فشل إضافة المنتج إلى السلة');
        }
    }

    async updateCartCount() {
        try {
            const cartItems = await API.get('cart/items');
            document.getElementById('cart-link').textContent = `السلة (${cartItems.length})`;
        } catch (error) {
            console.log('Failed to update cart count');
        }
    }

    showAuthModal() {
        const modal = document.getElementById('auth-modal');
        const container = document.getElementById('auth-form-container');
        
        container.innerHTML = `
            <h2>تسجيل دخول</h2>
            <form id="login-form" onsubmit="app.handleLogin(event)">
                <div class="form-group">
                    <label>البريد الإلكتروني:</label>
                    <input type="email" id="login-email" required>
                </div>
                <div class="form-group">
                    <label>كلمة المرور:</label>
                    <input type="password" id="login-password" required>
                </div>
                <button type="submit">تسجيل دخول</button>
            </form>
            <p>ليس لديك حساب؟ <a href="#" onclick="app.showRegisterForm()">إنشاء حساب</a></p>
        `;
        modal.style.display = 'block';
    }

    showRegisterForm() {
        const container = document.getElementById('auth-form-container');
        container.innerHTML = `
            <h2>إنشاء حساب جديد</h2>
            <form id="register-form" onsubmit="app.handleRegister(event)">
                <div class="form-group">
                    <label>الاسم:</label>
                    <input type="text" id="register-name" required>
                </div>
                <div class="form-group">
                    <label>البريد الإلكتروني:</label>
                    <input type="email" id="register-email" required>
                </div>
                <div class="form-group">
                    <label>رقم الهاتف:</label>
                    <input type="tel" id="register-phone">
                </div>
                <div class="form-group">
                    <label>كلمة المرور:</label>
                    <input type="password" id="register-password" required>
                </div>
                <button type="submit">إنشاء حساب</button>
            </form>
            <p>لديك حساب بالفعل؟ <a href="#" onclick="app.showAuthModal()">تسجيل دخول</a></p>
        `;
    }

    async handleLogin(event) {
        event.preventDefault();
        const email = document.getElementById('login-email').value;
        const password = document.getElementById('login-password').value;

        try {
            const response = await API.post('auth/login', { email, password });
            this.user = response.user;
            this.updateAuthLink();
            document.getElementById('auth-modal').style.display = 'none';
            alert('تم تسجيل الدخول بنجاح');
        } catch (error) {
            alert('فشل تسجيل الدخول');
        }
    }

    async handleRegister(event) {
        event.preventDefault();
        const name = document.getElementById('register-name').value;
        const email = document.getElementById('register-email').value;
        const phone = document.getElementById('register-phone').value;
        const password = document.getElementById('register-password').value;

        try {
            await API.post('auth/register', { name, email, phone, password });
            alert('تم إنشاء الحساب بنجاح. يرجى تسجيل الدخول');
            this.showAuthModal();
        } catch (error) {
            alert('فشل إنشاء الحساب');
        }
    }

    async logout() {
        try {
            await API.post('auth/logout', {});
            this.user = null;
            document.getElementById('auth-link').textContent = 'تسجيل دخول';
            document.getElementById('auth-link').onclick = () => this.showAuthModal();
            alert('تم تسجيل الخروج بنجاح');
        } catch (error) {
            alert('فشل تسجيل الخروج');
        }
    }

    showCart() {
        alert('ميزة السلة قيد التطوير');
    }
}

// Initialize app
const app = new App();