<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NO_KFC — Панель Администратора</title>
    @include('partials.panel-styles')
    <style>
        .tab-nav{display:flex;gap:4px;padding:20px 24px 0;background:var(--bg-header,#111)}
        .tab-btn{padding:10px 20px;border:none;background:transparent;color:#aaa;font-size:.875rem;font-family:inherit;cursor:pointer;border-bottom:2px solid transparent;transition:color .15s,border-color .15s;white-space:nowrap}
        .tab-btn.active{color:#fff;border-bottom-color:#e8372a}
        .tab-btn:hover:not(.active){color:#fff}
        .tab-pane{display:none}
        .tab-pane.active{display:block}

        .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:900;align-items:center;justify-content:center}
        .modal-overlay.open{display:flex}
        .modal{background:#1a1a1a;border:1px solid #333;border-radius:12px;padding:28px;width:min(520px,95vw);max-height:90vh;overflow-y:auto}
        .modal-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}
        .modal-title{font-size:1.1rem;font-weight:600;color:#fff}
        .modal-close{background:none;border:none;color:#888;font-size:1.4rem;cursor:pointer;line-height:1;padding:0 4px}
        .modal-close:hover{color:#fff}

        .order-card{background:#161616;border:1px solid #2a2a2a;border-radius:10px;padding:16px 18px;margin-bottom:10px}
        .order-meta{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-bottom:8px}
        .order-id{font-weight:700;font-size:.9rem;color:#fff}
        .order-addr{font-size:.82rem;color:#888;flex:1;min-width:120px}
        .order-total{font-weight:600;color:#e8372a;font-size:.95rem;margin-left:auto}
        .order-items-row{font-size:.8rem;color:#999;margin:4px 0 10px}
        .order-footer{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
        .order-actions{margin-left:auto;display:flex;gap:6px}

        .status-badge{display:inline-block;padding:3px 9px;border-radius:20px;font-size:.75rem;font-weight:500}
        .s-new{background:#1e3a5f;color:#64b5f6}
        .s-confirmed{background:#1b3a2a;color:#66bb6a}
        .s-preparing{background:#3a2a0a;color:#ffb74d}
        .s-on_the_way{background:#2a1a3a;color:#ce93d8}
        .s-delivered{background:#1a2a1a;color:#a5d6a7}
        .s-cancelled{background:#3a1a1a;color:#ef9a9a}

        .filter-bar{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px}
        .filter-bar select,.filter-bar input{flex:1;min-width:120px}

        .edit-row{display:none;gap:8px;flex-wrap:wrap;align-items:flex-end;background:#1e1e1e;border:1px solid #333;border-radius:8px;padding:14px;margin-top:10px}
        .edit-row.open{display:flex}
        .edit-row .field{flex:1;min-width:110px;margin-bottom:0}

        .btn-icon{background:transparent;border:1px solid #333;border-radius:6px;color:#aaa;padding:5px 9px;cursor:pointer;font-size:.8rem;transition:background .15s,color .15s}
        .btn-icon:hover{background:#333;color:#fff}
        .btn-icon.btn-edit{border-color:#2a4a6a;color:#64b5f6}
        .btn-icon.btn-edit:hover{background:#1e3a5f}
        .btn-icon.btn-del{border-color:#4a1a1a;color:#ef9a9a}
        .btn-icon.btn-del:hover{background:#3a1a1a}
        .btn-save{background:#e8372a;border:none;color:#fff;border-radius:6px;padding:6px 14px;cursor:pointer;font-size:.82rem;font-weight:500}
        .btn-save:hover{background:#c72d22}
        .btn-cancel-edit{background:transparent;border:1px solid #444;color:#aaa;border-radius:6px;padding:6px 12px;cursor:pointer;font-size:.82rem}

        .img-upload-area{border:2px dashed #333;border-radius:8px;padding:16px;text-align:center;cursor:pointer;transition:border-color .2s;position:relative}
        .img-upload-area:hover{border-color:#e8372a}
        .img-upload-area input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
        .img-upload-area p{color:#666;font-size:.82rem;margin:0;pointer-events:none}
        .img-preview{width:100%;max-height:160px;object-fit:cover;border-radius:6px;margin-top:8px;display:none}
        .dish-thumb{width:48px;height:48px;object-fit:cover;border-radius:6px;flex-shrink:0}
        .dish-thumb-placeholder{width:48px;height:48px;background:#222;border-radius:6px;flex-shrink:0;display:flex;align-items:center;justify-content:center;color:#444;font-size:1.2rem}

        /* Чекбоксы состава */
        .ingredient-selector {max-height: 150px; overflow-y: auto; background: #111; border: 1px solid #333; border-radius: 6px; padding: 10px; margin-top: 5px;}
        .ing-row-select {display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; font-size: 0.8rem;}
        .ing-row-select input[type="number"] {width: 60px; padding: 2px 4px; background: #222; border: 1px solid #444; color: #fff;}
    </style>
</head>
<body>
<div class="panel-wrap" style="max-width:100%;padding:0;">

    <header class="panel-header">
        <div class="panel-header-strip"></div>
        <div class="panel-header-inner">
            <div class="panel-brand">
                <h1 class="panel-title">NO_KFC</h1>
                <p class="panel-subtitle">Администрирование</p>
            </div>
            <div class="panel-actions">
                <span class="user-pill" title="{{ auth()->user()->email }}">{{ auth()->user()->name }}</span>
                <button class="btn btn-ghost btn-sm" type="button" onclick="loadAll()">Обновить</button>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button class="btn btn-ghost" type="submit">Выйти</button>
                </form>
            </div>
        </div>
    </header>

    <div id="status" class="flash">Готово.</div>

    <nav class="tab-nav">
        <button class="tab-btn active" onclick="switchTab('orders')">Заказы</button>
        <button class="tab-btn"        onclick="switchTab('dishes')">Меню</button>
        <button class="tab-btn"        onclick="switchTab('inventory')">Склад</button>
        <button class="tab-btn"        onclick="switchTab('staff')">Персонал</button>
    </nav>

    <div id="tab-orders" class="tab-pane active" style="padding:20px 24px;">
        <div class="filter-bar">
            <select id="order-filter-status" onchange="loadOrders()">
                <option value="">Все статусы</option>
                <option value="new">Новый</option>
                <option value="confirmed">Подтверждён</option>
                <option value="preparing">Готовится</option>
                <option value="on_the_way">В пути</option>
                <option value="delivered">Доставлен</option>
                <option value="cancelled">Отменён</option>
            </select>
            <input id="order-search" type="text" placeholder="Поиск по адресу / клиенту…" oninput="filterOrdersLocally()">
        </div>
        <div id="orders-list"><div class="empty-state">Загрузка…</div></div>
    </div>

    <div id="tab-dishes" class="tab-pane" style="padding:20px 24px;">
        <div class="grid-2">
            <section class="card">
                <div class="card-head"><span class="chip">Добавить</span><h2 class="card-title">Новое блюдо</h2></div>
                <div class="field"><label>Название</label><input id="dish-name" type="text" placeholder="Баскет 6 крыльев"></div>
                
                <div class="row-2">
                    <div class="field"><label>Цена, ₽</label><input id="dish-price" type="number" step="0.01" min="0.01" placeholder="0.00"></div>
                    <div class="field"><label>В продаже</label>
                        <select id="dish-active"><option value="1" selected>Да</option><option value="0">Нет</option></select>
                    </div>
                </div>

                <div class="field">
                    <label>Время приготовления (минут)</label>
                    <input id="dish-prep-time" type="number" min="1" value="15">
                </div>

                <div class="field">
                    <label>Необходимые ингредиенты (состав рецепта)</label>
                    <div id="dish-ingredients-selector" class="ingredient-selector">
                        </div>
                </div>

                <div class="field"><label>Описание</label><textarea id="dish-description" placeholder="Краткое описание"></textarea></div>
                
                <div class="field">
                    <label>Фото блюда</label>
                    <div class="img-upload-area" onclick="document.getElementById('dish-image').click()">
                        <input id="dish-image" type="file" accept="image/*" onchange="previewImage(this,'dish-preview')">
                        <p>Нажмите или перетащите файл</p>
                    </div>
                    <img id="dish-preview" class="img-preview" alt="Предпросмотр">
                </div>
                <button class="btn btn-primary btn-block" onclick="createDish()">Добавить блюдо</button>
            </section>

            <section class="card">
                <div class="card-head"><span class="chip">Каталог</span><h2 class="card-title">Список блюд</h2></div>
                <div id="dishes-list" class="list-stack"><div class="empty-state">Загрузка…</div></div>
            </section>
        </div>
    </div>

    <div id="tab-inventory" class="tab-pane" style="padding:20px 24px;">
        <div class="grid-2">
            <section class="card">
                <div class="card-head"><span class="chip">Поставка</span><h2 class="card-title">Добавить на склад</h2></div>
                <div class="field"><label>Название ингредиента</label><input id="ing-name" type="text" placeholder="Куриное филе (шт)"></div>
                <div class="field"><label>Количество</label><input id="ing-qty" type="number" min="0" value="0"></div>
                <button class="btn btn-primary btn-block" onclick="createIngredient()">Добавить на склад</button>
            </section>
            <section class="card">
                <div class="card-head"><span class="chip">Остатки</span><h2 class="card-title">Текущий склад</h2></div>
                <div id="inventory-list" class="list-stack"><div class="empty-state">Загрузка…</div></div>
            </section>
        </div>
    </div>

    <div id="tab-staff" class="tab-pane" style="padding:20px 24px;">
        <div class="grid-2">
            <section class="card">
                <div class="card-head"><span class="chip">Добавить</span><h2 class="card-title">Новый сотрудник</h2></div>
                <div class="field"><label>Имя</label><input id="staff-name" type="text" placeholder="ФИО или позывной"></div>
                <div class="field"><label>Email</label><input id="staff-email" type="email" placeholder="staff@example.com"></div>
                <div class="field"><label>Пароль</label><input id="staff-password" type="password" placeholder="Не менее 8 символов"></div>
                <div class="field"><label>Роль</label>
                    <select id="staff-role"><option value="kitchen">Кухня</option><option value="courier">Курьер</option><option value="admin">Администратор</option></select>
                </div>
                <button class="btn btn-primary btn-block" onclick="createStaff()">Создать сотрудника</button>
            </section>
            <section class="card">
                <div class="card-head"><span class="chip">Персонал</span><h2 class="card-title">Сотрудники</h2></div>
                <div id="staff-list" class="list-stack"><div class="empty-state">Загрузка…</div></div>
            </section>
        </div>
    </div>
</div>

<div id="order-modal" class="modal-overlay" onclick="closeOrderModal(event)">
    <div class="modal">
        <div class="modal-head"><span class="modal-title" id="modal-order-title">Заказ #…</span><button class="modal-close" onclick="closeOrderModal()">&times;</button></div>
        <div id="modal-order-body">Загрузка…</div>
        <div style="margin-top:20px;padding-top:16px;border-top:1px solid #2a2a2a;">
            <label style="font-size:.82rem;color:#888;display:block;margin-bottom:6px;">Изменить статус</label>
            <div style="display:flex;gap:8px;flex-wrap:wrap;"><select id="modal-status-select" style="flex:1;min-width:140px;"></select><button class="btn-save" onclick="updateOrderStatus()">Сохранить</button></div>
        </div>
        <div style="margin-top:14px;">
            <label style="font-size:.82rem;color:#888;display:block;margin-bottom:6px;">Назначить курьера</label>
            <div style="display:flex;gap:8px;flex-wrap:wrap;"><select id="modal-courier-select" style="flex:1;min-width:140px;"></select><button class="btn-save" onclick="assignCourier()">Назначить</button></div>
        </div>
        <div style="margin-top:14px;">
            <label style="font-size:.82rem;color:#888;display:block;margin-bottom:6px;">Заметка (Время готовки)</label>
            <div style="display:flex;gap:8px;"><textarea id="modal-note" rows="2" style="flex:1;resize:vertical;"></textarea><button class="btn-save" style="align-self:flex-end;" onclick="saveOrderNote()">Сохранить</button></div>
        </div>
        <div style="margin-top:20px;padding-top:16px;border-top:1px solid #2a2a2a;"><button class="btn btn-danger-outline btn-sm" onclick="deleteCurrentOrder()">Удалить заказ</button></div>
    </div>
</div>

<script>
const token    = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const statusEl = document.getElementById('status');
let allOrders  = [];
let couriers   = [];
let ingredients = []; // Локальный кэш ингредиентов
let currentOrderId = null;

const roleRu   = {admin:'Админ',kitchen:'Кухня',courier:'Курьер'};
const statusRu = {new:'Новый',confirmed:'Подтверждён',preparing:'Готовится',on_the_way:'В пути',delivered:'Доставлен',cancelled:'Отменён'};
const STATUSES = ['new','confirmed','preparing','on_the_way','delivered','cancelled'];

function setStatus(text,isError=false){statusEl.style.display='block';statusEl.textContent=text;statusEl.classList.toggle('is-error',isError)}
function esc(s){return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;')}

async function api(url,method='GET',body=null){
    const r=await fetch(url,{method,headers:{'Content-Type':'application/json','X-CSRF-TOKEN':token,'Accept':'application/json'},body:body?JSON.stringify(body):null});
    if(r.status===204)return null;
    if(!r.ok){const e=await r.json().catch(()=>({}));throw new Error(e.message||'Ошибка');}
    return r.json();
}
async function apiForm(url,formData){
    const r=await fetch(url,{method:'POST',headers:{'X-CSRF-TOKEN':token,'Accept':'application/json'},body:formData});
    if(r.status===204)return null;
    if(!r.ok){const e=await r.json().catch(()=>({}));throw new Error(e.message||'Ошибка');}
    return r.json();
}

function statusBadge(s){return`<span class="status-badge s-${s}">${statusRu[s]||s}</span>`}

function switchTab(name){
    document.querySelectorAll('.tab-pane').forEach(p=>p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
    document.getElementById('tab-'+name).classList.add('active');
    document.querySelector(`.tab-btn[onclick="switchTab('${name}')"]`).classList.add('active');
}

function previewImage(input,previewId){
    const preview=document.getElementById(previewId);
    if(input.files&&input.files[0]){
        const reader=new FileReader();
        reader.onload=e=>{preview.src=e.target.result;preview.style.display='block'};
        reader.readAsDataURL(input.files[0]);
    }else{preview.style.display='none'}
}

/* ══ ORDERS ══ */
async function loadOrders(){
    const list=document.getElementById('orders-list');
    try{
        const status=document.getElementById('order-filter-status').value;
        allOrders=await api('/app-api/admin/orders'+(status?'?status='+status:''));
        couriers=(await api('/app-api/admin/staff')).filter(u=>u.role==='courier');
        renderOrders(allOrders);
    }catch(e){list.innerHTML=`<div class="empty-state">${esc(e.message)}</div>`}
}
function renderOrders(orders){
    const list=document.getElementById('orders-list');
    if(!orders.length){list.innerHTML='<div class="empty-state">Заказов нет.</div>';return}
    list.innerHTML=orders.map(o=>`
        <div class="order-card">
            <div class="order-meta"><span class="order-id">#${o.id}</span>${statusBadge(o.status)}<span class="order-addr">${esc(o.delivery_address||'—')}</span><span class="order-total">${Number(o.total_amount||0).toFixed(2)} ₽</span></div>
            <div class="order-items-row">${o.items?.map(i=>`${esc(i.dish?.name||'?')} × ${i.quantity}`).join(', ')||'Нет позиций'}</div>
            <div class="order-footer">
                <span style="font-size:.78rem;color:#e8372a;">⏱ ${esc(o.delivery_time || 'Не указано')}</span>
                <span style="font-size:.78rem;color:#666;margin-left:10px;">${o.customer?.name?esc(o.customer.name):'Клиент'}${o.courier?' · 🛵 '+esc(o.courier.name):''}</span>
                <div class="order-actions">
                    <button class="btn-icon btn-edit" onclick="openOrderModal(${o.id})">Открыть</button>
                    <button class="btn-icon btn-del" onclick="deleteOrder(${o.id})">Удалить</button>
                </div>
            </div>
        </div>`).join('');
}
function filterOrdersLocally(){const q=document.getElementById('order-search').value.toLowerCase();renderOrders(q?allOrders.filter(o=>(o.delivery_address||'').toLowerCase().includes(q)||(o.customer?.name||'').toLowerCase().includes(q)):allOrders)}
async function openOrderModal(id){
    currentOrderId=id;const o=allOrders.find(x=>x.id===id);if(!o)return;
    document.getElementById('modal-order-title').textContent=`Заказ #${o.id}`;
    document.getElementById('modal-order-body').innerHTML=`<table style="width:100%;font-size:.85rem;border-collapse:collapse;">
        <tr><td style="color:#888;padding:4px 0;width:130px;">Адрес</td><td>${esc(o.delivery_address||'—')}</td></tr>
        <tr><td style="color:#888;padding:4px 0;">Расчетное время</td><td><strong>${esc(o.delivery_time||'—')}</strong></td></tr>
        <tr><td style="color:#888;padding:4px 0;">Сумма</td><td style="color:#e8372a;font-weight:600;">${Number(o.total_amount||0).toFixed(2)} ₽</td></tr>
        <tr><td style="color:#888;padding:4px 0;">Позиции</td><td>${o.items?.map(i=>`${esc(i.dish?.name||'?')} × ${i.quantity}`).join('<br>')||'—'}</td></tr>
    </table>`;
    const ss=document.getElementById('modal-status-select');
    ss.innerHTML=STATUSES.map(s=>`<option value="${s}" ${s===o.status?'selected':''}>${statusRu[s]}</option>`).join('');
    const cs=document.getElementById('modal-courier-select');
    cs.innerHTML='<option value="">Не назначен</option>'+couriers.map(c=>`<option value="${c.id}" ${c.id===o.courier_id?'selected':''}>${esc(c.name)}</option>`).join('');
    document.getElementById('modal-note').value=o.note||'';
    document.getElementById('order-modal').classList.add('open');
}
function closeOrderModal(e){if(!e||e.target===document.getElementById('order-modal'))document.getElementById('order-modal').classList.remove('open')}
async function updateOrderStatus(){try{await api(`/app-api/admin/orders/${currentOrderId}`,'PATCH',{status:document.getElementById('modal-status-select').value});setStatus('Статус обновлён.');await loadOrders()}catch(e){setStatus(e.message,true)}}
async function assignCourier(){try{await api(`/app-api/admin/orders/${currentOrderId}`,'PATCH',{courier_id:document.getElementById('modal-courier-select').value||null});setStatus('Курьер назначен.');await loadOrders()}catch(e){setStatus(e.message,true)}}
async function saveOrderNote(){try{await api(`/app-api/admin/orders/${currentOrderId}`,'PATCH',{note:document.getElementById('modal-note').value.trim()});setStatus('Заметка сохранена.');await loadOrders()}catch(e){setStatus(e.message,true)}}
async function deleteOrder(id){if(!confirm('Удалить заказ #'+id+'?'))return;try{await api(`/app-api/admin/orders/${id}`,'DELETE');setStatus('Заказ удалён.');document.getElementById('order-modal').classList.remove('open');await loadOrders()}catch(e){setStatus(e.message,true)}}
async function deleteCurrentOrder(){if(currentOrderId)await deleteOrder(currentOrderId)}


/* ══ INVENTORY (УПРАВЛЕНИЕ СКЛАДОМ) ══ */
async function loadInventory() {
    const list = document.getElementById('inventory-list');
    const selector = document.getElementById('dish-ingredients-selector');
    try {
        ingredients = await api('/app-api/admin/ingredients');
        
        // Рендерим блок списка склада
        if(!ingredients.length) {
            list.innerHTML = '<div class="empty-state">Склад пуст.</div>';
            selector.innerHTML = '<span class="muted">Сначала добавьте ингредиенты на склад.</span>';
            return;
        }
        
        list.innerHTML = ingredients.map(i => `
            <div class="list-item">
                <div style="flex:1;"><strong>${esc(i.name)}</strong><div class="muted" style="margin-top:4px;">Доступно: ${i.quantity} шт.</div></div>
                <div style="display:flex;gap:6px;align-items:center;">
                    <input type="number" id="inv-qty-edit-${i.id}" value="${i.quantity}" style="width:70px;padding:4px;" min="0">
                    <button class="btn-save" style="padding:4px 8px;" onclick="updateIngredientQty(${i.id})">Обновить</button>
                    <button class="btn-icon btn-del" onclick="deleteIngredient(${i.id})">×</button>
                </div>
            </div>
        `).join('');

        // Обновляем список селекторов состава для создания нового блюда
        selector.innerHTML = ingredients.map(i => `
            <div class="ing-row-select">
                <label><input type="checkbox" class="new-dish-ing-check" value="${i.id}"> ${esc(i.name)}</label>
                <div>Расход: <input type="number" class="new-dish-ing-amount" id="ing-amount-${i.id}" value="1" min="1"></div>
            </div>
        `).join('');

    } catch (e) { setStatus(e.message, true); }
}

async function createIngredient() {
    const name = document.getElementById('ing-name').value.trim();
    const quantity = parseInt(document.getElementById('ing-qty').value) || 0;
    if(!name) return setStatus('Укажите название ингредиента', true);
    try {
        await api('/app-api/admin/ingredients', 'POST', { name, quantity });
        document.getElementById('ing-name').value = '';
        document.getElementById('ing-qty').value = '0';
        setStatus('Ингредиент добавлен на склад.');
        await loadInventory();
    } catch(e) { setStatus(e.message, true); }
}

async function updateIngredientQty(id) {
    const qty = parseInt(document.getElementById(`inv-qty-edit-${id}`).value) || 0;
    try {
        await api(`/app-api/admin/ingredients/${id}`, 'PUT', { quantity: qty });
        setStatus('Количество обновлено.');
        await loadAll(); // Перезагружаем всё, так как доступность блюд могла измениться
    } catch(e) { setStatus(e.message, true); }
}

async function deleteIngredient(id) {
    if(!confirm('Удалить ингредиент со склада?')) return;
    try {
        await api(`/app-api/admin/ingredients/${id}`, 'DELETE');
        setStatus('Ингредиент удален.');
        await loadAll();
    } catch(e) { setStatus(e.message, true); }
}


/* ══ DISHES (БЛЮДА) ══ */
async function loadDishes(){
    const list=document.getElementById('dishes-list');
    try{
        const dishes = await api('/app-api/admin/dishes');
        if(!dishes.length){list.innerHTML='<div class="empty-state">Блюд нет.</div>';return}
        
        list.innerHTML=dishes.map(d=> {
            // Собираем читаемый состав блюда
            const compText = d.ingredients?.map(i => `${esc(i.name)} (x${i.pivot.amount})`).join(', ') || 'Состав не указан';
            
            // Генерируем чекбоксы для редактирования этого блюда
            const editIngsHtml = ingredients.map(ing => {
                const activeIng = d.ingredients?.find(x => x.id === ing.id);
                return `
                    <div class="ing-row-select">
                        <label><input type="checkbox" class="edit-dish-ing-check-${d.id}" value="${ing.id}" ${activeIng?'checked':''}> ${esc(ing.name)}</label>
                        <div>Расход: <input type="number" class="edit-dish-ing-amount-${d.id}" id="edit-ing-amount-${d.id}-${ing.id}" value="${activeIng ? activeIng.pivot.amount : 1}" min="1"></div>
                    </div>`;
            }).join('');

            return `
            <div class="list-item" id="dish-row-${d.id}" style="gap:12px;align-items:flex-start;">
                ${d.image_url ? `<img src="${esc(d.image_url)}" class="dish-thumb" alt="">` : `<div class="dish-thumb-placeholder">🍽</div>`}
                <div style="flex:1;min-width:0;">
                    <strong>${esc(d.name)}</strong>
                    <span class="badge" style="margin-left:8px;">${Number(d.price).toFixed(2)} ₽</span>
                    <span class="badge" style="background:#333;color:#ffb74d;margin-left:4px;">⏱ ${d.preparation_time} мин</span>
                    <div class="muted" style="margin-top:4px; font-size:0.75rem; color:#ff8a80;">Состав: ${compText}</div>
                    <div class="muted" style="margin-top:2px;">${d.description?esc(d.description):'Без описания'}</div>
                </div>
                <div style="display:flex;gap:6px;flex-shrink:0;">
                    <button class="btn-icon btn-edit" onclick="toggleDishEdit(${d.id})">Изменить</button>
                    <button class="btn-icon btn-del"  onclick="deleteDish(${d.id})">Удалить</button>
                </div>
            </div>
            <div class="edit-row" id="dish-edit-${d.id}">
                <div class="field"><label>Название</label><input id="de-name-${d.id}" type="text" value="${esc(d.name)}"></div>
                <div class="field"><label>Цена ₽</label><input id="de-price-${d.id}" type="number" step="0.01" value="${d.price}"></div>
                <div class="field"><label>Время (мин)</label><input id="de-time-${d.id}" type="number" value="${d.preparation_time}"></div>
                <div class="field"><label>Статус</label>
                    <select id="de-active-${d.id}"><option value="1" ${d.is_active?'selected':''}>В продаже</option><option value="0" ${!d.is_active?'selected':''}>Скрыто</option></select></div>
                
                <div class="field" style="flex:100%;"><label>Редактировать состав блюда</label>
                    <div class="ingredient-selector">${editIngsHtml}</div>
                </div>

                <div class="field" style="flex:2;min-width:160px;"><label>Описание</label><input id="de-desc-${d.id}" type="text" value="${esc(d.description||'')}"></div>
                <div class="field" style="flex:2;min-width:160px;">
                    <label>Новое фото</label>
                    <div class="img-upload-area" style="padding:10px;" onclick="document.getElementById('de-img-${d.id}').click()">
                        <input id="de-img-${d.id}" type="file" accept="image/*" onchange="previewImage(this,'de-prev-${d.id}')">
                        <p style="font-size:.75rem;">Нажмите для замены</p>
                    </div>
                    <img id="de-prev-${d.id}" class="img-preview">
                </div>
                <button class="btn-save" onclick="saveDish(${d.id})">Сохранить</button>
                <button class="btn-cancel-edit" onclick="toggleDishEdit(${d.id})">Отмена</button>
            </div>`;
        }).join('');
    }catch(e){list.innerHTML=`<div class="empty-state">${esc(e.message)}</div>`}
}

function toggleDishEdit(id){document.getElementById('dish-edit-'+id).classList.toggle('open')}

// Сборка массива ингредиентов для отправки на сервер
function getIngredientsData(checkClass, amountClassSelector) {
    const data = [];
    document.querySelectorAll(checkClass).forEach(cb => {
        if(cb.checked) {
            const ingId = cb.value;
            const amountInput = document.querySelector(`${amountClassSelector}${ingId}`);
            data.push({ id: parseInt(ingId), amount: parseInt(amountInput.value) || 1 });
        }
    });
    return JSON.stringify(data);
}

async function createDish(){
    const name=document.getElementById('dish-name').value.trim();
    const price=Number(document.getElementById('dish-price').value);
    const prep_time=parseInt(document.getElementById('dish-prep-time').value) || 15;
    
    if(!name||!price||price<=0){setStatus('Укажите название и цену.',true);return}
    
    const fd=new FormData();
    fd.append('name',name);
    fd.append('price',price);
    fd.append('preparation_time', prep_time);
    fd.append('description',document.getElementById('dish-description').value.trim());
    fd.append('is_active',document.getElementById('dish-active').value);
    fd.append('ingredients', getIngredientsData('.new-dish-ing-check', '#ing-amount-'));

    const imgFile=document.getElementById('dish-image').files[0];
    if(imgFile)fd.append('image',imgFile);
    
    try{
        await apiForm('/app-api/admin/dishes',fd);
        setStatus('Блюдо добавлено.');
        ['dish-name','dish-price','dish-description'].forEach(id=>document.getElementById(id).value='');
        document.getElementById('dish-image').value='';
        document.getElementById('dish-preview').style.display='none';
        await loadAll();
    }catch(e){setStatus(e.message,true)}
}

async function saveDish(id){
    const name=document.getElementById(`de-name-${id}`).value.trim();
    const price=Number(document.getElementById(`de-price-${id}`).value);
    const prep_time=parseInt(document.getElementById(`de-time-${id}`).value) || 15;
    
    if(!name||!price||price<=0){setStatus('Укажите название и цену.',true);return}
    
    const fd=new FormData();
    fd.append('_method','PUT');
    fd.append('name',name);
    fd.append('price',price);
    fd.append('preparation_time', prep_time);
    fd.append('is_active',document.getElementById(`de-active-${id}`).value);
    fd.append('description',document.getElementById(`de-desc-${id}`).value.trim());
    fd.append('ingredients', getIngredientsData(`.edit-dish-ing-check-${id}`, `#edit-ing-amount-${id}-`));

    const imgFile=document.getElementById(`de-img-${id}`).files[0];
    if(imgFile)fd.append('image',imgFile);
    try{
        await apiForm(`/app-api/admin/dishes/${id}`,fd);
        setStatus('Блюдо обновлено.');
        await loadAll();
    }catch(e){setStatus(e.message,true)}
}

async function deleteDish(id){
    if(!confirm('Удалить блюдо?'))return;
    try{await api(`/app-api/admin/dishes/${id}`,'DELETE');setStatus('Блюдо удалено.');await loadAll()}
    catch(e){setStatus(e.message,true)}
}

/* ══ STAFF ══ */
async function loadStaff(){
    const list=document.getElementById('staff-list');
    try{
        const staff=await api('/app-api/admin/staff');
        if(!staff.length){list.innerHTML='<div class="empty-state">Сотрудников нет.</div>';return}
        list.innerHTML=staff.map(u=>`
            <div class="list-item" id="staff-row-${u.id}">
                <div style="flex:1;"><strong>${esc(u.name)}</strong><span class="badge" style="margin-left:8px;">${roleRu[u.role]||u.role}</span><div class="muted" style="margin-top:4px;">${esc(u.email)}</div></div>
                <div style="display:flex;gap:6px;">
                    <button class="btn-icon btn-edit" onclick="toggleStaffEdit(${u.id})">Изменить</button>
                    <button class="btn-icon btn-del"  onclick="deleteStaff(${u.id})">Удалить</button>
                </div>
            </div>
            <div class="edit-row" id="staff-edit-${u.id}">
                <div class="field"><label>Имя</label><input id="se-name-${u.id}" type="text" value="${esc(u.name)}"></div>
                <div class="field"><label>Email</label><input id="se-email-${u.id}" type="email" value="${esc(u.email)}"></div>
                <div class="field"><label>Роль</label>
                    <select id="se-role-${u.id}">
                        <option value="kitchen" ${u.role==='kitchen'?'selected':''}>Кухня</option>
                        <option value="courier" ${u.role==='courier'?'selected':''}>Курьер</option>
                        <option value="admin"   ${u.role==='admin'?'selected':''}>Администратор</option>
                    </select></div>
                <div class="field"><label>Новый пароль</label><input id="se-pass-${u.id}" type="password" placeholder="Оставьте пустым"></div>
                <button class="btn-save" onclick="saveStaff(${u.id})">Сохранить</button>
                <button class="btn-cancel-edit" onclick="toggleStaffEdit(${u.id})">Отмена</button>
            </div>`).join('');
    }catch(e){list.innerHTML=`<div class="empty-state">${esc(e.message)}</div>`}
}
function toggleStaffEdit(id){document.getElementById('staff-edit-'+id).classList.toggle('open')}
async function saveStaff(id){
    const body={name:document.getElementById(`se-name-${id}`).value.trim(),email:document.getElementById(`se-email-${id}`).value.trim(),role:document.getElementById(`se-role-${id}`).value};
    const pass=document.getElementById(`se-pass-${id}`).value;if(pass)body.password=pass;
    try{await api(`/app-api/admin/staff/${id}`,'PUT',body);setStatus('Сотрудник обновлён.');await loadStaff()}catch(e){setStatus(e.message,true)}
}
async function createStaff(){
    try{await api('/app-api/admin/staff','POST',{name:document.getElementById('staff-name').value.trim(),email:document.getElementById('staff-email').value.trim(),password:document.getElementById('staff-password').value,role:document.getElementById('staff-role').value});
    setStatus('Сотрудник создан.');['staff-name','staff-email','staff-password'].forEach(id=>document.getElementById(id).value='');await loadStaff()}catch(e){setStatus(e.message,true)}
}
async function deleteStaff(id){if(!confirm('Удалить учётную запись?'))return;try{await api(`/app-api/admin/staff/${id}`,'DELETE');setStatus('Запись удалена.');await loadStaff()}catch(e){setStatus(e.message,true)}}

// Глобальная перезагрузка зависимых данных
async function loadAll(){
    // Сначала загружаем склад, так как от него зависит рендеринг состава блюд
    await loadInventory(); 
    await Promise.all([loadOrders(), loadDishes(), loadStaff()]);
    setStatus('Данные успешно обновлены.', false);
}
loadAll();
</script>
</body>
</html>