<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NO_KFC Delivery Panel</title>
    <style>
        :root {
            --brand-red: #e4002b;
            --brand-red-dark: #bf0024;
            --brand-black: #111111;
            --brand-white: #ffffff;
            --brand-bg: #f4f4f4;
            --brand-border: #e7e7e7;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            background: linear-gradient(180deg, #fbfbfb 0%, #f2f2f2 100%);
            color: var(--brand-black);
        }
        .container { max-width: 1180px; margin: 0 auto; padding: 24px 16px 40px; }
        .header {
            background: var(--brand-white);
            border: 1px solid var(--brand-border);
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
        }
        .header-top {
            height: 12px;
            background: repeating-linear-gradient(90deg, var(--brand-red), var(--brand-red) 30px, #ffffff 30px, #ffffff 60px);
        }
        .header-main {
            padding: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }
        .brand-wrap { display: flex; flex-direction: column; gap: 4px; }
        h1 { margin: 0; font-size: 30px; letter-spacing: 1px; font-weight: 900; color: var(--brand-red); }
        .subtitle { margin: 0; color: #575757; font-size: 13px; text-transform: uppercase; letter-spacing: .9px; font-weight: 700; }
        .btn-refresh {
            width: auto;
            padding: 10px 14px;
            border: 1px solid var(--brand-black);
            border-radius: 10px;
            background: var(--brand-white);
            color: var(--brand-black);
            cursor: pointer;
            font-weight: 700;
        }
        .btn-refresh:hover { background: #f7f7f7; }
        .header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .user-badge {
            font-size: 13px;
            font-weight: 700;
            color: #4b4b4b;
            background: #fff4f6;
            border: 1px solid #ffd6df;
            border-radius: 999px;
            padding: 6px 10px;
            white-space: nowrap;
        }
        .btn-logout {
            width: auto;
            padding: 10px 14px;
            border: 1px solid var(--brand-red);
            border-radius: 10px;
            background: #fff4f6;
            color: var(--brand-red);
            cursor: pointer;
            font-weight: 700;
        }
        .btn-logout:hover { background: #ffe9ef; }
        .status {
            margin: 0 0 16px;
            padding: 12px 14px;
            border-radius: 10px;
            background: #fff4f6;
            border: 1px solid #ffd6df;
            color: #7e2435;
            font-size: 14px;
        }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px; }
        .card {
            background: var(--brand-white);
            border-radius: 14px;
            border: 1px solid var(--brand-border);
            padding: 14px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
        }
        .card-head {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }
        .chip {
            background: var(--brand-red);
            color: var(--brand-white);
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .4px;
            padding: 3px 9px;
        }
        h2 { margin: 0; font-size: 18px; }
        .row { display: flex; gap: 8px; margin-bottom: 8px; }
        input, select, textarea, button {
            width: 100%;
            padding: 10px;
            border: 1px solid #d9d9d9;
            border-radius: 9px;
            background: #fff;
            color: var(--brand-black);
            font-size: 14px;
        }
        textarea { min-height: 70px; resize: vertical; }
        input:focus, select:focus, textarea:focus { outline: 2px solid #ffd4de; border-color: var(--brand-red); }
        button {
            border: 0;
            background: linear-gradient(180deg, var(--brand-red), var(--brand-red-dark));
            color: var(--brand-white);
            cursor: pointer;
            font-weight: 700;
            transition: transform .12s ease, filter .12s ease;
        }
        button:hover { transform: translateY(-1px); filter: brightness(1.05); }
        hr { border: 0; height: 1px; background: var(--brand-border); margin: 12px 0; }
        ul { margin: 0; padding-left: 18px; }
        li { margin: 8px 0; line-height: 1.35; }
        .muted { color: #6a6a6a; font-size: 13px; }
        .badge {
            display: inline-block;
            border-radius: 999px;
            padding: 2px 9px;
            background: #ffe6ec;
            color: #9f1736;
            border: 1px solid #ffcfda;
            font-size: 12px;
            font-weight: 700;
        }
    </style>
</head>
<body>
<div class="container">
    <header class="header">
        <div class="header-top"></div>
        <div class="header-main">
            <div class="brand-wrap">
                <h1>NO_KFC</h1>
                <p class="subtitle">Delivery CRM Panel</p>
            </div>
            <div class="header-actions">
                <span class="user-badge">{{ auth()->user()->email }}</span>
                <button class="btn-refresh" type="button" onclick="loadAll()">Refresh all</button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn-logout" type="submit">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <div id="status" class="status">Ready. Use forms below to test API.</div>

    <div class="grid">
        <section class="card">
            <div class="card-head">
                <span class="chip">NO_KFC</span>
                <h2>Create customer</h2>
            </div>
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
            <div class="card-head">
                <span class="chip">MENU</span>
                <h2>Create dish</h2>
            </div>
            <div class="row"><input id="dish-name" placeholder="Dish name"></div>
            <div class="row"><input id="dish-price" type="number" step="0.01" placeholder="Price"></div>
            <div class="row"><textarea id="dish-description" placeholder="Description"></textarea></div>
            <button onclick="createDish()">Create dish</button>
            <hr>
            <h2>Dishes</h2>
            <ul id="dishes-list"></ul>
        </section>

        <section class="card">
            <div class="card-head">
                <span class="chip">ORDERS</span>
                <h2>Create order</h2>
            </div>
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
    statusEl.style.background = isError ? "#ffe8ed" : "#fff4f6";
    statusEl.style.borderColor = isError ? "#ffc9d6" : "#ffd6df";
    statusEl.style.color = isError ? "#9f1736" : "#7e2435";
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
