<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOKFC CRM Demo</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f6f7fb; color: #222; }
        .container { max-width: 1100px; margin: 24px auto; padding: 0 16px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px; }
        .card { background: #fff; border-radius: 10px; padding: 14px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); }
        h1, h2 { margin: 0 0 10px; }
        h1 { font-size: 22px; }
        h2 { font-size: 18px; }
        .row { display: flex; gap: 8px; margin-bottom: 8px; }
        input, select, textarea, button { width: 100%; padding: 8px; border: 1px solid #d9dbe4; border-radius: 6px; }
        textarea { min-height: 68px; resize: vertical; }
        button { background: #222; color: #fff; cursor: pointer; }
        button.secondary { background: #fff; color: #222; }
        ul { margin: 0; padding-left: 18px; }
        li { margin: 6px 0; }
        .muted { color: #666; font-size: 13px; }
        .status { margin: 12px 0; padding: 10px; border-radius: 8px; background: #eef3ff; }
        .badge { display: inline-block; border-radius: 999px; padding: 2px 8px; background: #eee; font-size: 12px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>NOKFC CRM Demo</h1>
        <button class="secondary" onclick="loadAll()">Refresh all</button>
    </div>

    <div id="status" class="status muted">Ready. Use forms below to test API.</div>

    <div class="grid">
        <section class="card">
            <h2>Create customer</h2>
            <div class="row"><input id="customer-name" placeholder="Name"></div>
            <div class="row"><input id="customer-phone" placeholder="Phone (+7999...)"></div>
            <div class="row"><input id="customer-address" placeholder="Address"></div>
            <div class="row"><textarea id="customer-comment" placeholder="Comment"></textarea></div>
            <button onclick="createCustomer()">Create customer</button>
            <hr>
            <h2>Customers</h2>
            <ul id="customers-list"></ul>
        </section>

        <section class="card">
            <h2>Create dish</h2>
            <div class="row"><input id="dish-name" placeholder="Dish name"></div>
            <div class="row"><input id="dish-price" type="number" step="0.01" placeholder="Price"></div>
            <div class="row"><textarea id="dish-description" placeholder="Description"></textarea></div>
            <button onclick="createDish()">Create dish</button>
            <hr>
            <h2>Dishes</h2>
            <ul id="dishes-list"></ul>
        </section>

        <section class="card">
            <h2>Create order</h2>
            <div class="row">
                <select id="order-customer"></select>
            </div>
            <div class="row">
                <select id="order-dish-1"></select>
                <input id="order-qty-1" type="number" min="1" value="1">
            </div>
            <div class="row">
                <select id="order-dish-2"></select>
                <input id="order-qty-2" type="number" min="1" value="1">
            </div>
            <div class="row"><input id="order-address" placeholder="Delivery address"></div>
            <div class="row"><textarea id="order-note" placeholder="Order note"></textarea></div>
            <button onclick="createOrder()">Create order</button>
            <hr>
            <h2>Orders</h2>
            <ul id="orders-list"></ul>
        </section>
    </div>
</div>

<script>
const api = "/api/v1";
const statusEl = document.getElementById("status");

function setStatus(text, isError = false) {
    statusEl.textContent = text;
    statusEl.style.background = isError ? "#ffecec" : "#eef3ff";
}

async function request(url, options = {}) {
    const response = await fetch(url, {
        headers: { "Content-Type": "application/json", ...(options.headers || {}) },
        ...options
    });

    let data = null;
    try { data = await response.json(); } catch (_) {}

    if (!response.ok) {
        throw new Error(data?.message || "Request failed");
    }
    return data;
}

async function loadCustomers() {
    const data = await request(`${api}/customers`);
    const customers = data.data || [];
    const list = document.getElementById("customers-list");
    const select = document.getElementById("order-customer");
    list.innerHTML = "";
    select.innerHTML = "";

    customers.forEach(c => {
        const li = document.createElement("li");
        li.innerHTML = `<strong>${c.name}</strong> <span class="badge">${c.phone}</span><br><span class="muted">${c.address}</span>`;
        list.appendChild(li);

        const option = document.createElement("option");
        option.value = c.id;
        option.textContent = `${c.name} (${c.phone})`;
        select.appendChild(option);
    });
}

async function loadDishes() {
    const data = await request(`${api}/dishes`);
    const dishes = data.data || [];
    const list = document.getElementById("dishes-list");
    const dish1 = document.getElementById("order-dish-1");
    const dish2 = document.getElementById("order-dish-2");
    list.innerHTML = "";
    dish1.innerHTML = "";
    dish2.innerHTML = "";

    dishes.forEach(d => {
        const li = document.createElement("li");
        li.innerHTML = `<strong>${d.name}</strong> - ${d.price}`;
        list.appendChild(li);

        [dish1, dish2].forEach(select => {
            const option = document.createElement("option");
            option.value = d.id;
            option.textContent = `${d.name} (${d.price})`;
            select.appendChild(option);
        });
    });
}

async function loadOrders() {
    const data = await request(`${api}/orders`);
    const orders = data.data || [];
    const list = document.getElementById("orders-list");
    list.innerHTML = "";
    orders.forEach(o => {
        const li = document.createElement("li");
        li.innerHTML = `#${o.id} <span class="badge">${o.status}</span> total: <strong>${o.total_amount}</strong><br><span class="muted">${o.delivery_address}</span>`;
        list.appendChild(li);
    });
}

async function createCustomer() {
    try {
        await request(`${api}/customers`, {
            method: "POST",
            body: JSON.stringify({
                name: document.getElementById("customer-name").value,
                phone: document.getElementById("customer-phone").value,
                address: document.getElementById("customer-address").value,
                comment: document.getElementById("customer-comment").value
            })
        });
        setStatus("Customer created");
        await loadCustomers();
    } catch (e) {
        setStatus(`Customer error: ${e.message}`, true);
    }
}

async function createDish() {
    try {
        await request(`${api}/dishes`, {
            method: "POST",
            body: JSON.stringify({
                name: document.getElementById("dish-name").value,
                price: Number(document.getElementById("dish-price").value),
                description: document.getElementById("dish-description").value
            })
        });
        setStatus("Dish created");
        await loadDishes();
    } catch (e) {
        setStatus(`Dish error: ${e.message}`, true);
    }
}

async function createOrder() {
    try {
        const dish1 = Number(document.getElementById("order-dish-1").value);
        const dish2 = Number(document.getElementById("order-dish-2").value);
        const items = [
            { dish_id: dish1, quantity: Number(document.getElementById("order-qty-1").value || 1) }
        ];
        if (dish2 && dish2 !== dish1) {
            items.push({ dish_id: dish2, quantity: Number(document.getElementById("order-qty-2").value || 1) });
        }

        await request(`${api}/orders`, {
            method: "POST",
            body: JSON.stringify({
                customer_id: Number(document.getElementById("order-customer").value),
                delivery_address: document.getElementById("order-address").value,
                note: document.getElementById("order-note").value,
                items
            })
        });
        setStatus("Order created");
        await loadOrders();
    } catch (e) {
        setStatus(`Order error: ${e.message}`, true);
    }
}

async function loadAll() {
    try {
        await Promise.all([loadCustomers(), loadDishes(), loadOrders()]);
        setStatus("Data refreshed");
    } catch (e) {
        setStatus(`Load error: ${e.message}`, true);
    }
}

loadAll();
</script>
</body>
</html>
