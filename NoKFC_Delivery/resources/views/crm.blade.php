<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NO_KFC — Панель Администратора</title>
    @include('partials.panel-styles')
    <style>
        /* Настройки темы NO_KFC (Светлая, с красными акцентами) */
        body {
            background-color: #f4f6f8;
            color: #333;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
        }

        .tab-nav {
            display: flex;
            gap: 8px;
            padding: 16px 24px 0;
            background: #fff;
            border-bottom: 1px solid #e0e4ec;
        }
        .tab-btn {
            padding: 12px 24px;
            border: none;
            background: transparent;
            color: #64748b;
            font-size: .9rem;
            font-weight: 500;
            font-family: inherit;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: color .15s, border-color .15s;
            white-space: nowrap;
        }
        .tab-btn.active {
            color: #e8372a;
            border-bottom-color: #e8372a;
        }
        .tab-btn:hover:not(.active) {
            color: #1e293b;
        }
        .tab-pane {
            display: none;
        }
        .tab-pane.active {
            display: block;
        }

        /* Модальные окна */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(4px);
            z-index: 900;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.open {
            display: flex;
        }
        .modal {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 32px;
            width: min(540px, 95vw);
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .modal-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .modal-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
        }
        .modal-close {
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 1.6rem;
            cursor: pointer;
            line-height: 1;
            padding: 0 4px;
            transition: color .15s;
        }
        .modal-close:hover {
            color: #1e293b;
        }

        /* Карточки заказов */
        .order-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            transition: box-shadow .2s;
        }
        .order-card:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }
        .order-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            margin-bottom: 12px;
        }
        .order-id {
            font-weight: 700;
            font-size: 1rem;
            color: #1e293b;
        }
        .order-addr {
            font-size: .875rem;
            color: #64748b;
            flex: 1;
            min-width: 120px;
        }
        .order-total {
            font-weight: 700;
            color: #e8372a;
            font-size: 1.05rem;
            margin-left: auto;
        }
        .order-items-row {
            font-size: .875rem;
            color: #334155;
            background: #f8fafc;
            padding: 8px 12px;
            border-radius: 8px;
            margin: 8px 0 14px;
        }
        .order-footer {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }
        .order-actions {
            margin-left: auto;
            display: flex;
            gap: 8px;
        }

        /* Статус-бейджи */
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: .75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .s-new { background: #e0f2fe; color: #0369a1; }
        .s-confirmed { background: #dcfce7; color: #15803d; }
        .s-preparing { background: #fef3c7; color: #b45309; }
        .s-on_the_way { background: #f3e8ff; color: #6b21a8; }
        .s-delivered { background: #ecfdf5; color: #047857; }
        .s-cancelled { background: #fee2e2; color: #b91c1c; }

        /* Формы и поля */
        .filter-bar {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .filter-bar select, .filter-bar input, .field input, .field select, .field textarea, #modal-order-body select, textarea {
            background: #fff;
            border: 1px solid #cbd5e1;
            color: #1e293b;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: .875rem;
            transition: border-color .15s, box-shadow .15s;
        }
        .filter-bar select:focus, .filter-bar input:focus, .field input:focus, .field select:focus, .field textarea:focus {
            outline: none;
            border-color: #e8372a;
            box-shadow: 0 0 0 3px rgba(232, 55, 42, 0.15);
        }
        .filter-bar select, .filter-bar input {
            flex: 1;
            min-width: 140px;
        }

        /* Строки редактирования */
        .edit-row {
            display: none;
            gap: 12px;
            flex-wrap: wrap;
            align-items: flex-end;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            margin-top: 12px;
        }
        .edit-row.open {
            display: flex;
        }
        .edit-row .field {
            flex: 1;
            min-width: 120px;
            margin-bottom: 0;
        }

        /* Кнопки */
        .btn-icon {
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            color: #64748b;
            padding: 6px 12px;
            cursor: pointer;
            font-size: .85rem;
            font-weight: 500;
            transition: all .15s;
        }
        .btn-icon:hover {
            background: #f1f5f9;
            color: #1e293b;
            border-color: #94a3b8;
        }
        .btn-icon.btn-edit {
            border-color: #bae6fd;
            color: #0284c7;
            background: #f0f9ff;
        }
        .btn-icon.btn-edit:hover {
            background: #e0f2fe;
            color: #0369a1;
        }
        .btn-icon.btn-del {
            border-color: #fecaca;
            color: #dc2626;
            background: #fef2f2;
        }
        .btn-icon.btn-del:hover {
            background: #fee2e2;
            color: #b91c1c;
        }
        .btn-save {
            background: #e8372a;
            border: none;
            color: #fff;
            border-radius: 8px;
            padding: 8px 16px;
            cursor: pointer;
            font-size: .875rem;
            font-weight: 600;
            transition: background .15s;
        }
        .btn-save:hover {
            background: #c72d22;
        }
        .btn-cancel-edit {
            background: #fff;
            border: 1px solid #cbd5e1;
            color: #64748b;
            border-radius: 8px;
            padding: 8px 14px;
            cursor: pointer;
            font-size: .875rem;
            font-weight: 500;
            transition: background .15s;
        }
        .btn-cancel-edit:hover {
            background: #f1f5f9;
            color: #1e293b;
        }

        /* Изображения */
        .img-upload-area {
            border: 2px dashed #cbd5e1;
            background: #f8fafc;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: border-color .2s, background .2s;
            position: relative;
        }
        .img-upload-area:hover {
            border-color: #e8372a;
            background: #fff;
        }
        .img-upload-area input[type=file] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }
        .img-upload-area p {
            color: #64748b;
            font-size: .85rem;
            margin: 0;
            pointer-events: none;
        }
        .img-preview {
            width: 100%;
            max-height: 160px;
            object-fit: cover;
            border-radius: 8px;
            margin-top: 10px;
            display: none;
            border: 1px solid #e2e8f0;
        }
        .dish-thumb {
            width: 56px;
            height: 56px;
            object-fit: cover;
            border-radius: 8px;
            flex-shrink: 0;
            border: 1px solid #e2e8f0;
        }
        .dish-thumb-placeholder {
            width: 56px;
            height: 56px;
            background: #f1f5f9;
            border-radius: 8px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 1.4rem;
            border: 1px solid #e2e8f0;
        }

        /* Состав блюда */
        .ingredient-selector {
            max-height: 160px;
            overflow-y: auto;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 12px;
            margin-top: 6px;
        }
        .ing-row-select {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.85rem;
            color: #334155;
        }
        .ing-row-select:last-child { margin-bottom: 0; }
        .ing-row-select input[type="number"] {
            width: 65px;
            padding: 4px 6px;
            background: #fff;
            border: 1px solid #cbd5e1;
            color: #1e293b;
            border-radius: 4px;
        }
        
        .card {
            background: #fff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 12px !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
        }
        .muted { color: #64748b !important; }
        .empty-state { color: #94a3b8; padding: 40px 0; text-align: center; }
    </style>
</head>
<body>
<div class="panel-wrap" style="max-width:100%; padding:0;">

    <header class="panel-header" style="background:#fff; border-bottom:1px solid #e0e4ec;">
        <div class="panel-header-strip" style="background:#e8372a; height:4px;"></div>
        <div class="panel-header-inner" style="display:flex; align-items:center; padding:16px 24px;">
            <div class="panel-brand">
                <h1 class="panel-title" style="margin:0; color:#e8372a; font-size:1.5rem; font-weight:800; letter-spacing:-0.5px;">NO_KFC</h1>
                <p class="panel-subtitle" style="margin:2px 0 0; color:#64748b; font-size:0.8rem;">Администрирование</p>
            </div>
            <div class="panel-actions" style="display:flex; gap:12px; align-items:center; margin-left:auto;">
                <span class="user-pill" style="background:#f1f5f9; color:#334155; padding:6px 12px; border-radius:20px; font-size:0.85rem; font-weight:500;" title="{{ auth()->user()->email }}">{{ auth()->user()->name }}</span>
                <button class="btn-icon" type="button" onclick="loadAll()">Обновить</button>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button class="btn-icon btn-del" type="submit">Выйти</button>
                </form>
            </div>
        </div>
    </header>

    <div id="status" class="flash" style="display:none; background:#dcfce7; color:#15803d; border-left:4px solid #16a34a; padding:12px 24px; font-size:0.9rem; font-weight:500;">Готово.</div>

    <nav class="tab-nav">
        <button class="tab-btn active" onclick="switchTab('orders')">Заказы</button>
        <button class="tab-btn"        onclick="switchTab('dishes')">Меню</button>
        <button class="tab-btn"        onclick="switchTab('inventory')">Склад</button>
        <button class="tab-btn"        onclick="switchTab('staff')">Персонал</button>
    </nav>

    <div id="tab-orders" class="tab-pane active" style="padding:24px;">
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

    <div id="tab-dishes" class="tab-pane" style="padding:24px;">
        <div class="grid-2">
            <section class="card" style="padding:24px; margin-bottom:20px;">
                <div class="card-head" style="margin-bottom:20px;"><h2 class="card-title" style="margin:0; font-size:1.2rem; color:#1e293b;">Новое блюдо</h2></div>
                <div class="field" style="margin-bottom:16px;"><label style="display:block; margin-bottom:6px; font-size:0.85rem; font-weight:500;">Название</label><input id="dish-name" type="text" placeholder="Баскет 6 крыльев" style="width:100%; box-sizing:border-box;"></div>
                
                <div class="row-2" style="display:flex; gap:16px; margin-bottom:16px;">
                    <div class="field" style="flex:1;"><label style="display:block; margin-bottom:6px; font-size:0.85rem; font-weight:500;">Цена, ₽</label><input id="dish-price" type="number" step="0.01" min="0.01" placeholder="0.00" style="width:100%; box-sizing:border-box;"></div>
                    <div class="field" style="flex:1;"><label style="display:block; margin-bottom:6px; font-size:0.85rem; font-weight:500;">В продаже</label>
                        <select id="dish-active" style="width:100%; box-sizing:border-box;"><option value="1" selected>Да</option><option value="0">Нет</option></select>
                    </div>
                </div>

                <div class="field" style="margin-bottom:16px;">
                    <label style="display:block; margin-bottom:6px; font-size:0.85rem; font-weight:500;">Время приготовления (минут)</label>
                    <input id="dish-prep-time" type="number" min="1" value="15" style="width:100%; box-sizing:border-box;">
                </div>

                <div class="field" style="margin-bottom:16px;">
                    <label style="display:block; margin-bottom:6px; font-size:0.85rem; font-weight:500;">Необходимые ингредиенты (состав рецепта)</label>
                    <div id="dish-ingredients-selector" class="ingredient-selector"></div>
                </div>

                <div class="field" style="margin-bottom:16px;"><label style="display:block; margin-bottom:6px; font-size:0.85rem; font-weight:500;">Описание</label><textarea id="dish-description" placeholder="Краткое описание" style="width:100%; box-sizing:border-box; min-height:80px;"></textarea></div>
                
                <div class="field" style="margin-bottom:20px;">
                    <label style="display:block; margin-bottom:6px; font-size:0.85rem; font-weight:500;">Фото блюда</label>
                    <div class="img-upload-area" onclick="document.getElementById('dish-image').click()">
                        <input id="dish-image" type="file" accept="image/*" onchange="previewImage(this,'dish-preview')">
                        <p>Нажмите или перетащите файл</p>
                    </div>
                    <img id="dish-preview" class="img-preview" alt="Предпросмотр">
                </div>
                <button class="btn-save" style="width:100%; padding:12px;" onclick="createDish()">Добавить блюдо</button>
            </section>

            <section class="card" style="padding:24px;">
                <div class="card-head" style="margin-bottom:20px;"><h2 class="card-title" style="margin:0; font-size:1.2rem; color:#1e293b;">Список блюд</h2></div>
                <div id="dishes-list" class="list-stack"><div class="empty-state">Загрузка…</div></div>
            </section>
        </div>
    </div>

    <div id="tab-inventory" class="tab-pane" style="padding:24px;">
        <div class="grid-2">
            <section class="card" style="padding:24px; margin-bottom:20px;">
                <div class="card-head" style="margin-bottom:20px;"><h2 class="card-title" style="margin:0; font-size:1.2rem; color:#1e293b;">Добавить на склад</h2></div>
                <div class="field" style="margin-bottom:16px;"><label style="display:block; margin-bottom:6px; font-size:0.85rem; font-weight:500;">Название ингредиента</label><input id="ing-name" type="text" placeholder="Куриное филе (шт)" style="width:100%; box-sizing:border-box;"></div>
                <div class="field" style="margin-bottom:20px;"><label style="display:block; margin-bottom:6px; font-size:0.85rem; font-weight:500;">Количество</label><input id="ing-qty" type="number" min="0" value="0" style="width:100%; box-sizing:border-box;"></div>
                <button class="btn-save" style="width:100%; padding:12px;" onclick="createIngredient()">Добавить на склад</button>
            </section>
            <section class="card" style="padding:24px;">
                <div class="card-head" style="margin-bottom:20px;"><h2 class="card-title" style="margin:0; font-size:1.2rem; color:#1e293b;">Текущий склад</h2></div>
                <div id="inventory-list" class="list-stack"><div class="empty-state">Загрузка…</div></div>
            </section>
        </div>
    </div>

    <div id="tab-staff" class="tab-pane" style="padding:24px;">
        <div class="grid-2">
            <section class="card" style="padding:24px; margin-bottom:20px;">
                <div class="card-head" style="margin-bottom:20px;"><h2 class="card-title" style="margin:0; font-size:1.2rem; color:#1e293b;">Новый сотрудник</h2></div>
                <div class="field" style="margin-bottom:16px;"><label style="display:block; margin-bottom:6px; font-size:0.85rem; font-weight:500;">Имя</label><input id="staff-name" type="text" placeholder="ФИО или позывной" style="width:100%; box-sizing:border-box;"></div>
                <div class="field" style="margin-bottom:16px;"><label style="display:block; margin-bottom:6px; font-size:0.85rem; font-weight:500;">Email</label><input id="staff-email" type="email" placeholder="staff@example.com" style="width:100%; box-sizing:border-box;"></div>
                <div class="field" style="margin-bottom:16px;"><label style="display:block; margin-bottom:6px; font-size:0.85rem; font-weight:500;">Пароль</label><input id="staff-password" type="password" placeholder="Не менее 8 символов" style="width:100%; box-sizing:border-box;"></div>
                <div class="field" style="margin-bottom:20px;"><label style="display:block; margin-bottom:6px; font-size:0.85rem; font-weight:500;">Роль</label>
                    <select id="staff-role" style="width:100%; box-sizing:border-box;"><option value="kitchen">Кухня</option><option value="courier">Курьер</option><option value="admin">Администратор</option></select>
                </div>
                <button class="btn-save" style="width:100%; padding:12px;" onclick="createStaff()">Создать сотрудника</button>
            </section>
            <section class="card" style="padding:24px;">
                <div class="card-head" style="margin-bottom:20px;"><h2 class="card-title" style="margin:0; font-size:1.2rem; color:#1e293b;">Сотрудники</h2></div>
                <div id="staff-list" class="list-stack"><div class="empty-state">Загрузка…</div></div>
            </section>
        </div>
    </div>
</div>

<div id="order-modal" class="modal-overlay" onclick="closeOrderModal(event)">
    <div class="modal">
        <div class="modal-head"><span class="modal-title" id="modal-order-title">Заказ #…</span><button class="modal-close" onclick="closeOrderModal()">&times;</button></div>
        <div id="modal-order-body">Загрузка…</div>
        <div style="margin-top:24px; padding-top:20px; border-top:1px solid #e2e8f0;">
            <label style="font-size:.85rem; color:#64748b; display:block; margin-bottom:8px; font-weight:500;">Изменить статус</label>
            <div style="display:flex; gap:8px; flex-wrap:wrap;"><select id="modal-status-select" style="flex:1; min-width:140px;"></select><button class="btn-save" onclick="updateOrderStatus()">Сохранить</button></div>
        </div>
        <div style="margin-top:16px;">
            <label style="font-size:.85rem; color:#64748b; display:block; margin-bottom:8px; font-weight:500;">Назначить курьера</label>
            <div style="display:flex; gap:8px; flex-wrap:wrap;"><select id="modal-courier-select" style="flex:1; min-width:140px;"></select><button class="btn-save" onclick="assignCourier()">Назначить</button></div>
        </div>
        <div style="margin-top:16px;">
            <label style="font-size:.85rem; color:#64748b; display:block; margin-bottom:8px; font-weight:500;">Заметка (Время готовки)</label>
            <div style="display:flex; gap:8px; flex-wrap:wrap;"><textarea id="modal-note" rows="2" style="flex:1; min-width:200px; resize:vertical; box-sizing:border-box;"></textarea><button class="btn-save" style="align-self:flex-end;" onclick="saveOrderNote()">Сохранить</button></div>
        </div>
        <div style="margin-top:24px; padding-top:20px; border-top:1px solid #e2e8f0; text-align:right;"><button class="btn-icon btn-del" style="padding:8px 16px;" onclick="deleteCurrentOrder()">Удалить заказ</button></div>
    </div>
</div>

<script>
// Чтобы Blade не перехватывал шаблоны JS строк, экранируем через @{{ }} или конкатенацию.
const token    = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const statusEl = document.getElementById('status');
let allOrders  = [];
let couriers   = [];
let ingredients = []; 
let currentOrderId = null;

const roleRu   = {admin:'Админ',kitchen:'Кухня',courier:'Курьер'};
const statusRu = {new:'Новый',confirmed:'Подтверждён',preparing:'Готовится',on_the_way:'В пути',delivered:'Доставлен',cancelled:'Отменён'};
const STATUSES = ['new','confirmed','preparing','on_the_way','delivered','cancelled'];

function setStatus(text,isError=false){
    statusEl.style.display='block';
    statusEl.textContent=text;
    if(isError) {
        statusEl.style.background = '#fee2e2';
        statusEl.style.color = '#b91c1c';
        statusEl.style.borderLeft = '4px solid #ef4444';
    } else {
        statusEl.style.background = '#dcfce7';
        statusEl.style.color = '#15803d';
        statusEl.style.borderLeft = '4px solid #16a34a';
    }
}
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

function statusBadge(s){return '<span class="status-badge s-' + s + '">' + (statusRu[s]||s) + '</span>'}

function switchTab(name){
    document.querySelectorAll('.tab-pane').forEach(p=>p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
    document.getElementById('tab-'+name).classList.add('active');
    document.querySelector('.tab-btn[onclick="switchTab(\'' + name + '\')"]').classList.add('active');
}

function previewImage(input,previewId){
    const preview=document.getElementById(previewId);
    if(input.files&&input.files[0]){
        const reader=new FileReader();
        reader.onload=e=>{preview.src=e.target.result;preview.style.display='block'};
        reader.readAsDataURL(input.files[0]);
    }else{preview.style.display='none'}
}

/* ══ ИНИЦИАЛИЗАЦИЯ ВСЕХ ДАННЫХ ══ */
async function loadAll(){
    setStatus('Обновление данных…');
    await loadInventory();
    await Promise.all([loadOrders(), loadDishes(), loadStaff()]);
    statusEl.style.display='none';
}

/* ══ ORDERS ══ */
async function loadOrders(){
    const list=document.getElementById('orders-list');
    try{
        const status=document.getElementById('order-filter-status').value;
        allOrders=await api('/app-api/admin/orders'+(status?'?status='+status:''));
        couriers=(await api('/app-api/admin/staff')).filter(u=>u.role==='courier');
        renderOrders(allOrders);
    }catch(e){list.innerHTML='<div class="empty-state">' + esc(e.message) + '</div>'}
}
function renderOrders(orders){
    const list=document.getElementById('orders-list');
    if(!orders.length){list.innerHTML='<div class="empty-state">Заказов нет.</div>';return}
    
    // Переписано без использования коллизий синтаксиса Blade внутри шаблонных строк
    list.innerHTML=orders.map(o=> {
        const itemsStr = o.items ? o.items.map(i => esc(i.dish ? i.dish.name : '?') + ' × ' + i.quantity).join(', ') : 'Нет позиций';
        const clientName = o.customer && o.customer.name ? esc(o.customer.name) : 'Клиент';
        const courierStr = o.courier ? ' · 🛵 ' + esc(o.courier.name) : '';
        const totalAmount = Number(o.total_amount||0).toFixed(2);
        
        return '<div class="order-card">' +
            '<div class="order-meta"><span class="order-id">#' + o.id + '</span>' + statusBadge(o.status) + '<span class="order-addr">' + esc(o.delivery_address||'—') + '</span><span class="order-total">' + totalAmount + ' ₽</span></div>' +
            '<div class="order-items-row">' + itemsStr + '</div>' +
            '<div class="order-footer">' +
                '<span style="font-size:.85rem; font-weight:600; color:#e8372a;">⏱ ' + esc(o.delivery_time || 'Не указано') + '</span>' +
                '<span style="font-size:.85rem; color:#64748b; margin-left:10px;">' + clientName + courierStr + '</span>' +
                '<div class="order-actions">' +
                    '<button class="btn-icon btn-edit" onclick="openOrderModal(' + o.id + ')">Открыть</button>' +
                    '<button class="btn-icon btn-del" onclick="deleteOrder(' + o.id + ')">Удалить</button>' +
                '</div>' +
            '</div>' +
        '</div>';
    }).join('');
}

function filterOrdersLocally(){const q=document.getElementById('order-search').value.toLowerCase();renderOrders(q?allOrders.filter(o=>(o.delivery_address||'').toLowerCase().includes(q)||(o.customer?.name||'').toLowerCase().includes(q)):allOrders)}

async function openOrderModal(id){
    currentOrderId=id;const o=allOrders.find(x=>x.id===id);if(!o)return;
    document.getElementById('modal-order-title').textContent='Заказ #' + o.id;
    
    const itemsHtml = o.items ? o.items.map(i=> '<span style="background:#f8fafc; padding:2px 6px; border-radius:4px; display:inline-block; margin-bottom:4px;">' + esc(i.dish ? i.dish.name : '?') + ' <strong>× ' + i.quantity + '</strong></span>').join('<br>') : '—';
    
    document.getElementById('modal-order-body').innerHTML='<table style="width:100%; font-size:.9rem; border-collapse:collapse; color:#334155;">' +
        '<tr style="border-bottom:1px solid #f1f5f9;"><td style="color:#64748b; padding:8px 0; width:130px;">Адрес</td><td style="font-weight:500;">' + esc(o.delivery_address||'—') + '</td></tr>' +
        '<tr style="border-bottom:1px solid #f1f5f9;"><td style="color:#64748b; padding:8px 0;">Расчетное время</td><td><strong style="color:#1e293b;">' + esc(o.delivery_time||'—') + '</strong></td></tr>' +
        '<tr style="border-bottom:1px solid #f1f5f9;"><td style="color:#64748b; padding:8px 0;">Сумма</td><td style="color:#e8372a; font-weight:700;">' + Number(o.total_amount||0).toFixed(2) + ' ₽</td></tr>' +
        '<tr><td style="color:#64748b; padding:8px 0;">Позиции</td><td style="padding:8px 0; line-height:1.4;">' + itemsHtml + '</td></tr>' +
    '</table>';
    
    const ss=document.getElementById('modal-status-select');
    ss.innerHTML=STATUSES.map(s=> '<option value="' + s + '" ' + (s===o.status?'selected':'') + '>' + statusRu[s] + '</option>').join('');
    const cs=document.getElementById('modal-courier-select');
    cs.innerHTML='<option value="">Не назначен</option>'+couriers.map(c=> '<option value="' + c.id + '" ' + (c.id===o.courier_id?'selected':'') + '>' + esc(c.name) + '</option>').join('');
    document.getElementById('modal-note').value=o.note||'';
    document.getElementById('order-modal').classList.add('open');
}
function closeOrderModal(e){if(!e||e.target===document.getElementById('order-modal'))document.getElementById('order-modal').classList.remove('open')}
async function updateOrderStatus(){try{await api('/app-api/admin/orders/' + currentOrderId,'PATCH',{status:document.getElementById('modal-status-select').value});setStatus('Статус обновлён.');await loadOrders()}catch(e){setStatus(e.message,true)}}
async function assignCourier(){try{await api('/app-api/admin/orders/' + currentOrderId,'PATCH',{courier_id:document.getElementById('modal-courier-select').value||null});setStatus('Курьер назначен.');await loadOrders()}catch(e){setStatus(e.message,true)}}
async function saveOrderNote(){try{await api('/app-api/admin/orders/' + currentOrderId,'PATCH',{note:document.getElementById('modal-note').value.trim()});setStatus('Заметка сохранена.');await loadOrders()}catch(e){setStatus(e.message,true)}}
async function deleteOrder(id){if(!confirm('Удалить заказ #'+id+'?'))return;try{await api('/app-api/admin/orders/' + id,'DELETE');setStatus('Заказ удалён.');document.getElementById('order-modal').classList.remove('open');await loadOrders()}catch(e){setStatus(e.message,true)}}
async function deleteCurrentOrder(){if(currentOrderId)await deleteOrder(currentOrderId)}

/* ══ INVENTORY ══ */
async function loadInventory() {
    const list = document.getElementById('inventory-list');
    const selector = document.getElementById('dish-ingredients-selector');
    try {
        ingredients = await api('/app-api/admin/ingredients');
        if(!ingredients.length) {
            list.innerHTML = '<div class="empty-state">Склад пуст.</div>';
            selector.innerHTML = '<span class="muted">Сначала добавьте ингредиенты на склад.</span>';
            return;
        }
        
        list.innerHTML = ingredients.map(i => 
            '<div class="list-item" style="display:flex; justify-content:space-between; align-items:center; background:#fff; padding:12px 16px; border:1px solid #e2e8f0; border-radius:8px; margin-bottom:8px;">' +
                '<div style="flex:1;"><strong>' + esc(i.name) + '</strong><div class="muted" style="margin-top:4px; font-size:0.8rem;">Доступно: ' + i.quantity + ' шт.</div></div>' +
                '<div style="display:flex; gap:8px; align-items:center;">' +
                    '<input type="number" id="inv-qty-edit-' + i.id + '" value="' + i.quantity + '" style="width:70px; padding:6px; border:1px solid #cbd5e1; border-radius:6px;" min="0">' +
                    '<button class="btn-save" style="padding:6px 12px; font-size:0.8rem;" onclick="updateIngredientQty(' + i.id + ')">Обновить</button>' +
                    '<button class="btn-icon btn-del" style="padding:6px 10px;" onclick="deleteIngredient(' + i.id + ')">×</button>' +
                '</div>' +
            '</div>'
        ).join('');

        selector.innerHTML = ingredients.map(i => 
            '<div class="ing-row-select">' +
                '<label style="display:flex; align-items:center; gap:6px;"><input type="checkbox" class="new-dish-ing-check" value="' + i.id + '"> ' + esc(i.name) + '</label>' +
                '<div>Расход: <input type="number" class="new-dish-ing-amount" id="ing-amount-' + i.id + '" value="1" min="1"></div>' +
            '</div>'
        ).join('');
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
    const qty = parseInt(document.getElementById('inv-qty-edit-' + id).value) || 0;
    try {
        await api('/app-api/admin/ingredients/' + id, 'PUT', { quantity: qty });
        setStatus('Количество обновлено.');
        await loadAll();
    } catch(e) { setStatus(e.message, true); }
}

async function deleteIngredient(id) {
    if(!confirm('Удалить ингредиент со склада?')) return;
    try {
        await api('/app-api/admin/ingredients/' + id, 'DELETE');
        setStatus('Ингредиент удален.');
        await loadAll();
    } catch(e) { setStatus(e.message, true); }
}

/* ══ DISHES ══ */
async function loadDishes(){
    const list=document.getElementById('dishes-list');
    try{
        const dishes = await api('/app-api/admin/dishes');
        if(!dishes.length){list.innerHTML='<div class="empty-state">Блюд нет.</div>';return}
        
        list.innerHTML=dishes.map(d=> {
            const compText = d.ingredients ? d.ingredients.map(i => esc(i.name) + ' (x' + i.pivot.amount + ')').join(', ') : 'Состав не указан';
            const editIngsHtml = ingredients.map(ing => {
                const activeIng = d.ingredients ? d.ingredients.find(x => x.id === ing.id) : null;
                return '<div class="ing-row-select">' +
                        '<label style="display:flex; align-items:center; gap:6px;"><input type="checkbox" class="edit-dish-ing-check-' + d.id + '" value="' + ing.id + '" ' + (activeIng?'checked':'') + '> ' + esc(ing.name) + '</label>' +
                        '<div>Расход: <input type="number" class="edit-dish-ing-amount-' + d.id + '" id="edit-ing-amount-' + d.id + '-' + ing.id + '" value="' + (activeIng ? activeIng.pivot.amount : 1) + '" min="1"></div>' +
                    '</div>';
            }).join('');

            const imgHtml = d.image_url ? '<img src="' + esc(d.image_url) + '" class="dish-thumb" alt="">' : '<div class="dish-thumb-placeholder">🍽</div>';

            return '<div class="list-item" id="dish-row-' + d.id + '" style="gap:16px; align-items:flex-start; padding:16px; background:#fff; border:1px solid #e2e8f0; border-radius:10px; margin-bottom:10px; display:flex;">' +
                imgHtml +
                '<div style="flex:1; min-width:0;">' +
                    '<strong style="font-size:1.05rem; color:#1e293b;">' + esc(d.name) + '</strong>' +
                    '<span class="badge" style="margin-left:8px; background:#fee2e2; color:#dc2626; padding:2px 8px; border-radius:4px; font-size:0.8rem; font-weight:600;">' + Number(d.price).toFixed(2) + ' ₽</span>' +
                    '<span class="badge" style="background:#f1f5f9; color:#475569; margin-left:4px; padding:2px 8px; border-radius:4px; font-size:0.8rem;">⏱ ' + d.preparation_time + ' мин</span>' +
                    '<div class="muted" style="margin-top:6px; font-size:0.8rem; color:#dc2626; font-weight:500;">Состав: ' + compText + '</div>' +
                    '<div class="muted" style="margin-top:4px; font-size:0.85rem;">' + (d.description?esc(d.description):'Без описания') + '</div>' +
                '</div>' +
                '<div style="display:flex; gap:6px; flex-shrink:0;">' +
                    '<button class="btn-icon btn-edit" onclick="toggleDishEdit(' + d.id + ')">Изменить</button>' +
                    '<button class="btn-icon btn-del"  onclick="deleteDish(' + d.id + ')">Удалить</button>' +
                '</div>' +
            '</div>' +
            '<div class="edit-row" id="dish-edit-' + d.id + '">' +
                '<div class="field"><label style="display:block; margin-bottom:4px; font-size:0.8rem; font-weight:500;">Название</label><input id="de-name-' + d.id + '" type="text" value="' + esc(d.name) + '" style="width:100%; box-sizing:border-box;"></div>' +
                '<div class="field"><label style="display:block; margin-bottom:4px; font-size:0.8rem; font-weight:500;">Цена ₽</label><input id="de-price-' + d.id + '" type="number" step="0.01" value="' + d.price + '" style="width:100%; box-sizing:border-box;"></div>' +
                '<div class="field"><label style="display:block; margin-bottom:4px; font-size:0.8rem; font-weight:500;">Время (мин)</label><input id="de-time-' + d.id + '" type="number" value="' + d.preparation_time + '" style="width:100%; box-sizing:border-box;"></div>' +
                '<div class="field"><label style="display:block; margin-bottom:4px; font-size:0.8rem; font-weight:500;">Статус</label>' +
                    '<select id="de-active-' + d.id + '" style="width:100%; box-sizing:border-box;"><option value="1" ' + (d.is_active?'selected':'') + '>В продаже</option><option value="0" ' + (!d.is_active?'selected':'') + '>Скрыто</option></select></div>' +
                
                '<div class="field" style="flex:100%; margin-top:8px;"><label style="display:block; margin-bottom:4px; font-size:0.8rem; font-weight:500;">Редактировать состав блюда</label>' +
                    '<div class="ingredient-selector">' + editIngsHtml + '</div>' +
                '</div>' +

                '<div class="field" style="flex:2; min-width:160px; margin-top:8px;"><label style="display:block; margin-bottom:4px; font-size:0.8rem; font-weight:500;">Описание</label><input id="de-desc-' + d.id + '" type="text" value="' + esc(d.description||'').trim() + '" style="width:100%; box-sizing:border-box;"></div>' +
                '<div class="field" style="flex:2; min-width:160px; margin-top:8px;">' +
                    '<label style="display:block; margin-bottom:4px; font-size:0.8rem; font-weight:500;">Новое фото</label>' +
                    '<div class="img-upload-area" style="padding:10px;" onclick="document.getElementById(\'de-img-' + d.id + '\').click()">' +
                        '<input id="de-img-' + d.id + '" type="file" accept="image/*" onchange="previewImage(this,\'de-prev-' + d.id + '\')">' +
                        '<p style="font-size:.75rem;">Нажмите для замены</p>' +
                    '</div>' +
                    '<img id="de-prev-' + d.id + '" class="img-preview">' +
                '</div>' +
                '<div style="flex:100%; display:flex; gap:8px; margin-top:12px; justify-content:flex-end;">' +
                    '<button class="btn-save" onclick="saveDish(' + d.id + ')">Сохранить</button>' +
                    '<button class="btn-cancel-edit" onclick="toggleDishEdit(' + d.id + ')">Отмена</button>' +
                '</div>' +
            '</div>';
        }).join('');
    }catch(e){list.innerHTML='<div class="empty-state">' + esc(e.message) + '</div>'}
}

function toggleDishEdit(id) { document.getElementById('dish-edit-' + id).classList.toggle('open'); }

function getIngredientsData(checkClass, amountClassSelector) {
    const data = [];
    document.querySelectorAll(checkClass).forEach(cb => {
        if(cb.checked) {
            const ingId = cb.value;
            const amountInput = document.querySelector(amountClassSelector + ingId);
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
    const name=document.getElementById('de-name-' + id).value.trim();
    const price=Number(document.getElementById('de-price-' + id).value);
    const prep_time=parseInt(document.getElementById('de-time-' + id).value) || 15;
    
    if(!name||!price||price<=0){setStatus('Укажите название и цену.',true);return}
    
    const fd=new FormData();
    fd.append('_method','PUT');
    fd.append('name',name);
    fd.append('price',price);
    fd.append('preparation_time', prep_time);
    fd.append('is_active',document.getElementById('de-active-' + id).value);
    fd.append('description',document.getElementById('de-desc-' + id).value.trim());
    fd.append('ingredients', getIngredientsData('.edit-dish-ing-check-' + id, '#edit-ing-amount-' + id + '-'));

    const imgFile=document.getElementById('de-img-' + id).files[0];
    if(imgFile)fd.append('image',imgFile);
    try{
        await apiForm('/app-api/admin/dishes/' + id, fd);
        setStatus('Блюдо обновлено.');
        await loadAll();
    }catch(e){setStatus(e.message,true)}
}

async function deleteDish(id){
    if(!confirm('Удалить блюдо?'))return;
    try{await api('/app-api/admin/dishes/' + id,'DELETE');setStatus('Блюдо удалено.');await loadAll()}
    catch(e){setStatus(e.message,true)}
}

/* ══ STAFF ══ */
async function loadStaff(){
    const list=document.getElementById('staff-list');
    try{
        const staff=await api('/app-api/admin/staff');
        if(!staff.length){list.innerHTML='<div class="empty-state">Сотрудников нет.</div>';return}
        list.innerHTML=staff.map(u=>
            '<div class="list-item" id="staff-row-' + u.id + '" style="display:flex; justify-content:space-between; align-items:center; padding:16px; background:#fff; border:1px solid #e2e8f0; border-radius:10px; margin-bottom:10px;">' +
                '<div style="flex:1;">' +
                    '<strong style="color:#1e293b; font-size:1rem;">' + esc(u.name) + '</strong>' +
                    '<span class="badge" style="margin-left:8px; background:#f1f5f9; color:#475569; padding:2px 8px; border-radius:4px; font-size:0.8rem;">' + (roleRu[u.role]||u.role) + '</span>' +
                    '<div class="muted" style="margin-top:4px; font-size:0.85rem;">' + esc(u.email) + '</div>' +
                '</div>' +
                '<div style="display:flex; gap:6px;">' +
                    '<button class="btn-icon btn-edit" onclick="toggleStaffEdit(' + u.id + ')">Изменить</button>' +
                    '<button class="btn-icon btn-del" onclick="deleteStaff(' + u.id + ')">×</button>' +
                '</div>' +
            '</div>' +
            '<div class="edit-row" id="staff-edit-' + u.id + '">' +
                '<div class="field"><label>Имя</label><input id="se-name-' + u.id + '" type="text" value="' + esc(u.name) + '"></div>' +
                '<div class="field"><label>Email</label><input id="se-email-' + u.id + '" type="email" value="' + esc(u.email) + '"></div>' +
                '<div class="field"><label>Новый пароль</label><input id="se-password-' + u.id + '" type="password" placeholder="Оставьте пустым"></div>' +
                '<div class="field"><label>Роль</label>' +
                    '<select id="se-role-' + u.id + '">' +
                        '<option value="kitchen" ' + (u.role==='kitchen'?'selected':'') + '>Кухня</option>' +
                        '<option value="courier" ' + (u.role==='courier'?'selected':'') + '>Курьер</option>' +
                        '<option value="admin" ' + (u.role==='admin'?'selected':'') + '>Админ</option>' +
                    '</select>' +
                '</div>' +
                '<div style="flex:100%; display:flex; gap:8px; justify-content:flex-end; margin-top:8px;">' +
                    '<button class="btn-save" onclick="saveStaff(' + u.id + ')">Сохранить</button>' +
                    '<button class="btn-cancel-edit" onclick="toggleStaffEdit(' + u.id + ')">Отмена</button>' +
                '</div>' +
            '</div>'
        ).join('');
    }catch(e){list.innerHTML='<div class="empty-state">' + esc(e.message) + '</div>'}
}

function toggleStaffEdit(id) { document.getElementById('staff-edit-' + id).classList.toggle('open'); }

async function createStaff(){
    const name=document.getElementById('staff-name').value.trim();
    const email=document.getElementById('staff-email').value.trim();
    const password=document.getElementById('staff-password').value;
    const role=document.getElementById('staff-role').value;
    if(!name||!email||!password){setStatus('Заполните все поля сотрудники.',true);return}
    try{
        await api('/app-api/admin/staff','POST',{name,email,password,role});
        setStatus('Сотрудник создан.');
        ['staff-name','staff-email','staff-password'].forEach(id=>document.getElementById(id).value='');
        await loadAll();
    }catch(e){setStatus(e.message,true)}
}

async function saveStaff(id){
    const name=document.getElementById('se-name-' + id).value.trim();
    const email=document.getElementById('se-email-' + id).value.trim();
    const password=document.getElementById('se-password-' + id).value;
    const role=document.getElementById('se-role-' + id).value;
    if(!name||!email){setStatus('Имя и email обязательны.',true);return}
    const data={name,email,role};
    if(password)data.password=password;
    try{
        await api('/app-api/admin/staff/' + id,'PUT',data);
        setStatus('Данные сотрудника обновлены.');
        await loadAll();
    }catch(e){setStatus(e.message,true)}
}

async function deleteStaff(id){
    if(!confirm('Удалить сотрудника?'))return;
    try{await api('/app-api/admin/staff/' + id,'DELETE');setStatus('Сотрудник удален.');await loadAll()}
    catch(e){setStatus(e.message,true)}
}

document.addEventListener('DOMContentLoaded', loadAll);
</script>
</body>
</html>