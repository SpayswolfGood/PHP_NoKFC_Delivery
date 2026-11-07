<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NO_KFC — Меню и Бронирование</title>
    @include('partials.panel-styles')
    <style>
        :root{--accent-soft:#fff4f6;--accent-border:#ffd6df}

        /* ── Навигация по разделам ── */
        .section-nav {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            background: #fff;
            padding: 8px 12px;
            border-radius: 12px;
            border: 1px solid var(--brand-border, #e0e0e0);
            box-shadow: 0 2px 4px rgba(0,0,0,0.03);
        }
        .nav-tab-btn {
            padding: 10px 20px;
            border: none;
            background: transparent;
            font-size: .95rem;
            font-weight: 600;
            color: #64748b;
            border-radius: 8px;
            cursor: pointer;
            transition: all .2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .nav-tab-btn:hover {
            color: #e8372a;
            background: #fff4f6;
        }
        .nav-tab-btn.active {
            background: #e8372a;
            color: #fff;
            box-shadow: 0 2px 6px rgba(232, 55, 42, 0.25);
        }

        /* ── Сетка блюд ── */
        .dish-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;margin-top:12px}
        .dish-card{background:#ffffff;border:1px solid var(--accent-border, #e0e0e0);border-radius:10px;overflow:hidden;display:flex;flex-direction:column;transition:border-color .2s, box-shadow .2s;box-shadow: 0 2px 4px rgba(0,0,0,0.02)}
        .dish-card:hover{border-color:#e8372a;box-shadow: 0 4px 12px rgba(232,55,42,0.08)}
        .dish-card-img{width:100%;height:140px;object-fit:cover;display:block;background:var(--accent-soft, #f9f9f9);border-bottom:1px solid var(--accent-border, #f0f0f0)}
        .dish-card-img-placeholder{width:100%;height:140px;background:var(--accent-soft, #f9f9f9);display:flex;align-items:center;justify-content:center;font-size:2.5rem;color:#e8372a;border-bottom:1px solid var(--accent-border, #f0f0f0)}
        .dish-card-body{padding:12px;flex:1;display:flex;flex-direction:column;gap:6px}
        .dish-card-top{display:flex;justify-content:space-between;align-items:flex-start;gap:8px}
        .dish-card-top strong{font-size:.9rem;line-height:1.3;color:#222}
        .price-tag{font-size:.85rem;font-weight:700;color:#e8372a;white-space:nowrap}
        .dish-meta{font-size:.78rem;color:#666;flex:1;line-height:1.4;margin:0}
        .qty-row{display:flex;align-items:center;gap:8px;margin-top:auto;padding-top:8px;border-top:1px solid var(--accent-border, #f0f0f0)}
        .qty-row input[type=number]{width:56px;text-align:center;border:1px solid var(--accent-border, #d1d1d1);border-radius:4px;padding:2px 4px}
        .qty-row input[type=checkbox]{width:18px;height:18px;accent-color:#e8372a;cursor:pointer;flex-shrink:0}

        /* ── Сетка столиков ── */
        .tables-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(135px, 1fr));
            gap: 12px;
            margin: 14px 0;
        }
        .table-card {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px;
            text-align: center;
            cursor: pointer;
            transition: all .2s;
            background: #fff;
            position: relative;
            user-select: none;
        }
        .table-card.available {
            border-color: #86efac;
            background: #f0fdf4;
        }
        .table-card.available:hover {
            border-color: #22c55e;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(34, 197, 94, 0.15);
        }
        .table-card.selected {
            border-color: #e8372a !important;
            background: #fff5f5 !important;
            box-shadow: 0 0 0 3px rgba(232, 55, 42, 0.25) !important;
        }
        .table-card.occupied {
            border-color: #cbd5e1;
            background: #f1f5f9;
            opacity: 0.65;
            cursor: not-allowed;
        }
        .table-card.too-small {
            border-color: #fde68a;
            background: #fffbeb;
            opacity: 0.7;
            cursor: not-allowed;
        }
        .table-num {
            font-size: 1.1rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 4px;
        }
        .table-seats {
            font-size: .8rem;
            color: #475569;
            font-weight: 500;
        }
        .table-badge {
            display: inline-block;
            font-size: .7rem;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 6px;
            margin-top: 6px;
        }
        .table-badge.avail { background: #dcfce7; color: #15803d; }
        .table-badge.busy { background: #fee2e2; color: #b91c1c; }
        .table-badge.small { background: #fef3c7; color: #b45309; }

        .time-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
    </style>
</head>
<body>
<div class="panel-wrap">
    <header class="panel-header">
        <div class="panel-header-strip"></div>
        <div class="panel-header-inner">
            <div class="panel-brand">
                <h1 class="panel-title">NO_KFC</h1>
                <p class="panel-subtitle">Ресторан и доставка еды</p>
            </div>
            <div class="panel-actions">
                <span class="user-pill" title="{{ auth()->user()->email }}">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button class="btn btn-ghost" type="submit">Выйти</button>
                </form>
            </div>
        </div>
    </header>

    <!-- Вкладки верхнего уровня -->
    <nav class="section-nav">
        <button id="nav-btn-delivery" class="nav-tab-btn active" onclick="switchSection('delivery')">
            <span>🍕</span> Заказ еды на дом
        </button>
        <button id="nav-btn-booking" class="nav-tab-btn" onclick="switchSection('booking')">
            <span>🍽</span> Бронирование столика в ресторане
        </button>
    </nav>

    <div id="flash" class="flash" style="display:none;" role="status"></div>

    <!-- ── Раздел: Доставка блюд ── -->
    <div id="section-delivery">
        <div class="grid-2">
            <section class="card">
                <div class="card-head">
                    <span class="chip">Меню</span>
                    <h2 class="card-title">Выберите блюда</h2>
                </div>
                <div class="field">
                    <label for="address">Адрес доставки</label>
                    <input id="address" type="text" placeholder="Город, улица, дом, квартира" autocomplete="street-address">
                </div>
                <div class="field">
                    <label for="note">Комментарий к заказу</label>
                    <input id="note" type="text" placeholder="Например, домофон, время">
                </div>
                <div id="dishes-wrap">
                    <div class="empty-state">Загрузка меню…</div>
                </div>
                <hr class="sep">
                <button class="btn btn-primary btn-block" type="button" onclick="createOrder()">Оформить заказ</button>
            </section>

            <section class="card">
                <div class="card-head">
                    <span class="chip">Заказы</span>
                    <h2 class="card-title">Мои заказы</h2>
                </div>
                <div id="orders-wrap" class="list-stack">
                    <div class="empty-state">Загрузка…</div>
                </div>
            </section>
        </div>
    </div>

    <!-- ── Раздел: Бронирование столика ── -->
    <div id="section-booking" style="display:none;">
        <div class="grid-2">
            <section class="card">
                <div class="card-head">
                    <span class="chip" style="background:#fef2f2; color:#e8372a;">Столик</span>
                    <h2 class="card-title">Забронировать столик</h2>
                </div>

                <!-- Выбор адреса заведения -->
                <div class="field">
                    <label for="book-restaurant">Адрес заведения (ресторан сети)</label>
                    <select id="book-restaurant" onchange="refreshTables()">
                        <option value="">Загрузка ресторанов…</option>
                    </select>
                </div>

                <!-- Дата и Гости -->
                <div class="time-row">
                    <div class="field">
                        <label for="book-date">Дата бронирования</label>
                        <input id="book-date" type="date" onchange="refreshTables()">
                    </div>
                    <div class="field">
                        <label for="book-guests">Кол-во гостей (от 1 до 6)</label>
                        <select id="book-guests" onchange="refreshTables()">
                            <option value="1">1 гость</option>
                            <option value="2" selected>2 гостя</option>
                            <option value="3">3 гостя</option>
                            <option value="4">4 гостя</option>
                            <option value="5">5 гостей</option>
                            <option value="6">6 гостей</option>
                        </select>
                    </div>
                </div>

                <!-- Время от и до -->
                <div class="time-row">
                    <div class="field">
                        <label for="book-time-from">Время от</label>
                        <input id="book-time-from" type="time" value="18:00" onchange="refreshTables()">
                    </div>
                    <div class="field">
                        <label for="book-time-to">Время до</label>
                        <input id="book-time-to" type="time" value="20:00" onchange="refreshTables()">
                    </div>
                </div>

                <!-- Выбор доступного стола -->
                <div class="field" style="margin-top:10px;">
                    <label style="display:flex; justify-content:space-between; align-items:center;">
                        <span>Выберите свободный столик</span>
                        <span id="selected-table-label" style="font-weight:700; color:#e8372a;">Стол не выбран</span>
                    </label>
                    <div id="tables-container" class="tables-grid">
                        <div class="empty-state" style="grid-column:1/-1;">Выберите ресторан и время для просмотра столов</div>
                    </div>
                </div>

                <!-- Данные контакта -->
                <div class="time-row" style="margin-top:14px;">
                    <div class="field">
                        <label for="book-name">Ваше имя</label>
                        <input id="book-name" type="text" value="{{ auth()->user()->name }}" placeholder="Имя">
                    </div>
                    <div class="field">
                        <label for="book-phone">Номер телефона</label>
                        <input id="book-phone" type="tel" value="{{ auth()->user()->customer?->phone ?? '+7' }}" placeholder="+79991234567">
                    </div>
                </div>

                <div class="field">
                    <label for="book-note">Пожелания к бронированию (опционально)</label>
                    <input id="book-note" type="text" placeholder="Например, у окна, тихий угол, детский стульчик">
                </div>

                <hr class="sep">
                <button class="btn btn-primary btn-block" type="button" id="btn-submit-booking" onclick="createReservation()">
                    Подтвердить бронирование
                </button>
            </section>

            <!-- Мои бронирования -->
            <section class="card">
                <div class="card-head">
                    <span class="chip" style="background:#f0fdf4; color:#15803d;">Брони</span>
                    <h2 class="card-title">Мои бронирования</h2>
                </div>
                <div id="reservations-wrap" class="list-stack">
                    <div class="empty-state">Загрузка бронирований…</div>
                </div>
            </section>
        </div>
    </div>
</div>

<script>
const token   = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const flashEl = document.getElementById('flash');

const statusRu = {
    new:'Новый', preparing:'Готовится', confirmed:'Готов к выдаче',
    on_the_way:'В пути', delivered:'Доставлен', cancelled:'Отменён'
};

const resStatusRu = {
    confirmed: { text: 'Подтверждено', class: 'badge badge-ok' },
    completed: { text: 'Завершено', class: 'badge' },
    cancelled: { text: 'Отменено', class: 'badge badge-warn' }
};

function showFlash(text,isError=false){
    flashEl.style.display='block';
    flashEl.textContent=text;
    flashEl.classList.toggle('is-error',isError);
    if (!isError) {
        setTimeout(() => { flashEl.style.display='none'; }, 5000);
    }
}
function esc(s){return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;')}
function badgeClass(s){return s==='delivered'?'badge badge-ok':s==='cancelled'?'badge badge-warn':'badge'}

async function api(url,method='GET',body=null){
    const r=await fetch(url,{method,headers:{'Content-Type':'application/json','X-CSRF-TOKEN':token,'Accept':'application/json'},body:body?JSON.stringify(body):null});
    if(!r.ok){const e=await r.json().catch(()=>({}));throw new Error(e.message||(e.errors?Object.values(e.errors).flat().join(' '):'Ошибка запроса'))}
    if(r.status===204)return null;
    return r.json();
}

/* ── Переключение вкладок верхнего уровня ── */
function switchSection(sec) {
    const isDelivery = sec === 'delivery';
    document.getElementById('section-delivery').style.display = isDelivery ? 'block' : 'none';
    document.getElementById('section-booking').style.display = isDelivery ? 'none' : 'block';
    document.getElementById('nav-btn-delivery').classList.toggle('active', isDelivery);
    document.getElementById('nav-btn-booking').classList.toggle('active', !isDelivery);

    if (!isDelivery) {
        loadRestaurants().then(loadReservations);
    }
}

/* ── Меню и Заказы ── */
async function loadDishes(){
    const wrap=document.getElementById('dishes-wrap');
    try{
        const dishes=await api('/app-api/customer/dishes');
        if(!dishes.length){wrap.innerHTML='<div class="empty-state">В меню пока нет активных блюд.</div>';return}
        wrap.innerHTML=`<div class="dish-grid">${dishes.map(d=>`
            <article class="dish-card" data-id="${d.id}">
                ${d.image_url
                    ?`<img class="dish-card-img" src="${esc(d.image_url)}" alt="${esc(d.name)}" loading="lazy">`
                    :`<div class="dish-card-img-placeholder">🍽</div>`}
                <div class="dish-card-body">
                    <div class="dish-card-top">
                        <strong>${esc(d.name)}</strong>
                        <span class="price-tag">${Number(d.price).toFixed(2)} ₽</span>
                    </div>
                    <p class="dish-meta">${d.description?esc(d.description):'Без описания'}</p>
                    <div class="qty-row">
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:.8rem;font-weight:600;color:#222;">
                            <input type="checkbox" class="dish-check" value="${d.id}"> В корзину
                        </label>
                        <input type="number" class="dish-qty" data-id="${d.id}" value="1" min="1" style="margin-left:auto;" aria-label="Количество">
                    </div>
                </div>
            </article>`).join('')}</div>`;
    }catch(e){wrap.innerHTML=`<div class="empty-state">${esc(e.message)}</div>`;showFlash(e.message,true)}
}

async function loadOrders(){
    const wrap=document.getElementById('orders-wrap');
    try{
        const orders=await api('/app-api/customer/orders');
        if(!orders.length){wrap.innerHTML='<div class="empty-state">Вы ещё не делали заказов.</div>';return}
        wrap.innerHTML=orders.map(o=>`
            <div class="list-item">
                <div style="flex:1;">
                    <strong>№ ${o.id}</strong>
                    <span class="${badgeClass(o.status)}">${statusRu[o.status]||o.status}</span>
                    <div class="muted" style="margin-top:6px;">Сумма: <strong>${Number(o.total_amount).toFixed(2)} ₽</strong></div>
                    ${o.delivery_address?`<div class="muted">${esc(o.delivery_address)}</div>`:''}
                    ${o.note ? `<div class="muted" style="color: #e8372a; font-weight: 500;">${esc(o.note)}</div>` : ''}
                    ${o.delivery_time ? `<div class="muted">Ориентировочное время: <strong>${new Date(o.delivery_time).toLocaleTimeString('ru-RU', {hour: '2-digit', minute:'2-digit'})}</strong></div>` : ''}
                    ${o.items?.length?`<div class="muted" style="margin-top:4px;font-size:.8rem;">${o.items.map(i=>esc(i.dish?.name||'?')+' × '+i.quantity).join(', ')}</div>`:''}
                </div>
                ${o.status==='new'?`
                    <button class="btn btn-danger-outline btn-sm" style="align-self:flex-start;margin-left:12px;"
                        onclick="cancelOrder(${o.id},this)">Отменить</button>`:''}
            </div>`).join('');
    }catch(e){wrap.innerHTML=`<div class="empty-state">${esc(e.message)}</div>`;showFlash(e.message,true)}
}

async function cancelOrder(id,btn){
    if(!confirm('Отменить заказ № '+id+'?'))return;
    btn.disabled=true;btn.textContent='Отмена…';
    try{await api(`/app-api/customer/orders/${id}/cancel`,'PATCH');showFlash('Заказ № '+id+' отменён.',false);await loadOrders()}
    catch(e){showFlash(e.message,true);btn.disabled=false;btn.textContent='Отменить'}
}

async function createOrder(){
    const checked=Array.from(document.querySelectorAll('.dish-check:checked'));
    const items=checked.map(cb=>({dish_id:Number(cb.value),quantity:Number(document.querySelector(`.dish-qty[data-id="${cb.value}"]`)?.value||1)}));
    if(!items.length){showFlash('Выберите хотя бы одно блюдо.',true);return}
    const addr=document.getElementById('address').value.trim();
    if(!addr){showFlash('Укажите адрес доставки.',true);return}
    try{
        await api('/app-api/customer/orders','POST',{delivery_address:addr,note:document.getElementById('note').value.trim()||null,items});
        showFlash('Заказ оформлен.',false);
        await loadOrders();
        document.querySelectorAll('.dish-check').forEach(el=>el.checked=false);
    }catch(e){showFlash(e.message,true)}
}

/* ── Бронирование столиков ── */
let selectedTableId = null;
let restaurantsList = [];

// Установка сегодняшней даты по умолчанию
const todayStr = new Date().toISOString().split('T')[0];
document.getElementById('book-date').value = todayStr;
document.getElementById('book-date').min = todayStr;

async function loadRestaurants(){
    const sel = document.getElementById('book-restaurant');
    try {
        restaurantsList = await api('/app-api/customer/restaurants');
        if (!restaurantsList.length) {
            sel.innerHTML = '<option value="">Нет доступных ресторанов</option>';
            return;
        }
        sel.innerHTML = restaurantsList.map(r => `
            <option value="${r.id}">${esc(r.name)} — ${esc(r.address)} (${r.opening_time.slice(0,5)} - ${r.closing_time.slice(0,5)})</option>
        `).join('');
        await refreshTables();
    } catch(e) {
        sel.innerHTML = `<option value="">Ошибка загрузки</option>`;
        showFlash(e.message, true);
    }
}

async function refreshTables(){
    selectedTableId = null;
    document.getElementById('selected-table-label').textContent = 'Стол не выбран';
    const restId = document.getElementById('book-restaurant').value;
    const date = document.getElementById('book-date').value;
    const timeFrom = document.getElementById('book-time-from').value;
    const timeTo = document.getElementById('book-time-to').value;
    const guests = document.getElementById('book-guests').value;
    const container = document.getElementById('tables-container');

    if (!restId || !date || !timeFrom || !timeTo) {
        container.innerHTML = '<div class="empty-state" style="grid-column:1/-1;">Укажите все параметры бронирования</div>';
        return;
    }

    if (timeFrom >= timeTo) {
        container.innerHTML = '<div class="empty-state" style="grid-column:1/-1; color:#e8372a;">Время окончания должно быть позже времени начала</div>';
        return;
    }

    container.innerHTML = '<div class="empty-state" style="grid-column:1/-1;">Проверяем доступность столиков…</div>';

    try {
        const url = `/app-api/customer/tables/available?restaurant_id=${restId}&reservation_date=${date}&start_time=${timeFrom}&end_time=${timeTo}&guest_count=${guests}`;
        const tables = await api(url);

        if (!tables.length) {
            container.innerHTML = '<div class="empty-state" style="grid-column:1/-1;">В данном ресторане нет столиков</div>';
            return;
        }

        container.innerHTML = tables.map(t => {
            let cardClass = 'table-card ';
            let badgeHtml = '';
            let onClick = '';

            if (t.is_available) {
                cardClass += 'available';
                badgeHtml = '<span class="table-badge avail">✓ Свободен</span>';
                onClick = `onclick="selectTable(${t.id}, '${esc(t.table_number)}', ${t.seats})"`;
            } else if (t.is_occupied) {
                cardClass += 'occupied';
                badgeHtml = '<span class="table-badge busy">Занят</span>';
            } else if (t.too_small) {
                cardClass += 'too-small';
                badgeHtml = `<span class="table-badge small">Мало мест</span>`;
            }

            return `
                <div id="table-card-${t.id}" class="${cardClass}" ${onClick}>
                    <div class="table-num">Стол №${esc(t.table_number)}</div>
                    <div class="table-seats">🪑 ${t.seats} ${t.seats === 2 ? 'места' : t.seats < 5 ? 'места' : 'мест'}</div>
                    ${badgeHtml}
                </div>
            `;
        }).join('');
    } catch(e) {
        container.innerHTML = `<div class="empty-state" style="grid-column:1/-1;">${esc(e.message)}</div>`;
        showFlash(e.message, true);
    }
}

function selectTable(id, number, seats){
    selectedTableId = id;
    document.querySelectorAll('.table-card').forEach(c => c.classList.remove('selected'));
    const card = document.getElementById(`table-card-${id}`);
    if (card) card.classList.add('selected');
    document.getElementById('selected-table-label').textContent = `Выбран Стол №${number} (${seats} мест)`;
}

async function createReservation(){
    if (!selectedTableId) {
        showFlash('Пожалуйста, выберите свободный столик из списка.', true);
        return;
    }

    const date = document.getElementById('book-date').value;
    const timeFrom = document.getElementById('book-time-from').value;
    const timeTo = document.getElementById('book-time-to').value;
    const guests = parseInt(document.getElementById('book-guests').value, 10);
    const name = document.getElementById('book-name').value.trim();
    const phone = document.getElementById('book-phone').value.trim();
    const note = document.getElementById('book-note').value.trim();

    if (!name || !phone) {
        showFlash('Укажите имя и телефон для бронирования.', true);
        return;
    }

    const btn = document.getElementById('btn-submit-booking');
    btn.disabled = true;
    btn.textContent = 'Оформление брони…';

    try {
        const body = {
            restaurant_table_id: selectedTableId,
            reservation_date: date,
            start_time: timeFrom,
            end_time: timeTo,
            guest_count: guests,
            contact_name: name,
            contact_phone: phone,
            note: note || null
        };
        await api('/app-api/customer/reservations', 'POST', body);
        showFlash('Столик успешно забронирован! Ждем вас в гости.', false);
        await refreshTables();
        await loadReservations();
    } catch(e) {
        showFlash(e.message, true);
    } finally {
        btn.disabled = false;
        btn.textContent = 'Подтвердить бронирование';
    }
}

async function loadReservations(){
    const wrap = document.getElementById('reservations-wrap');
    try {
        const list = await api('/app-api/customer/reservations');
        if (!list.length) {
            wrap.innerHTML = '<div class="empty-state">У вас пока нет активных бронирований.</div>';
            return;
        }

        wrap.innerHTML = list.map(r => {
            const st = resStatusRu[r.status] || { text: r.status, class: 'badge' };
            const rest = r.table?.restaurant;
            const formattedDate = new Date(r.reservation_date).toLocaleDateString('ru-RU', {day: 'numeric', month: 'long', year: 'numeric'});

            return `
                <div class="list-item">
                    <div style="flex:1;">
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                            <strong>Стол №${esc(r.table?.table_number)}</strong>
                            <span class="${st.class}">${st.text}</span>
                        </div>
                        <div style="font-weight:600; color:#1e293b; margin-top:2px;">
                            ${esc(rest?.name || 'Ресторан')}
                        </div>
                        <div class="muted" style="font-size:.85rem;">
                            📍 ${esc(rest?.address || 'Адрес')}
                        </div>
                        <div class="muted" style="margin-top:4px;">
                            📅 <strong>${formattedDate}</strong> c <strong>${r.start_time.slice(0,5)}</strong> до <strong>${r.end_time.slice(0,5)}</strong>
                        </div>
                        <div class="muted" style="font-size:.85rem;">
                            👥 Гостей: <strong>${r.guest_count}</strong> | Мест за столом: <strong>${r.table?.seats}</strong>
                        </div>
                        ${r.note ? `<div class="muted" style="color:#e8372a; margin-top:2px;">Пожелания: ${esc(r.note)}</div>` : ''}
                    </div>
                    ${r.status === 'confirmed' ? `
                        <button class="btn btn-danger-outline btn-sm" style="align-self:flex-start; margin-left:12px;"
                            onclick="cancelReservation(${r.id}, this)">Отменить</button>
                    ` : ''}
                </div>
            `;
        }).join('');
    } catch(e) {
        wrap.innerHTML = `<div class="empty-state">${esc(e.message)}</div>`;
    }
}

async function cancelReservation(id, btn){
    if (!confirm('Отменить бронирование столика?')) return;
    btn.disabled = true;
    btn.textContent = 'Отмена…';
    try {
        await api(`/app-api/customer/reservations/${id}/cancel`, 'PATCH');
        showFlash('Бронирование отменено.', false);
        await loadReservations();
        await refreshTables();
    } catch(e) {
        showFlash(e.message, true);
        btn.disabled = false;
        btn.textContent = 'Отменить';
    }
}

// Первоначальная загрузка
loadDishes().then(loadOrders);
</script>
</body>
</html>