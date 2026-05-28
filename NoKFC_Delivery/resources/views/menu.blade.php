<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NO_KFC — Меню и заказы</title>
    @include('partials.panel-styles')
    <style>
        :root{--accent-soft:#fff4f6;--accent-border:#ffd6df}

        /* ── Dish grid & Светлый стиль NO_KFC ── */
        .dish-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;margin-top:12px}
        
        /* Карточка теперь белая, с мягкой тенью и рамкой в стиле остального сайта */
        .dish-card{background:#ffffff;border:1px solid var(--accent-border, #e0e0e0);border-radius:10px;overflow:hidden;display:flex;flex-direction:column;transition:border-color .2s, box-shadow .2s;box-shadow: 0 2px 4px rgba(0,0,0,0.02)}
        .dish-card:hover{border-color:#e8372a;box-shadow: 0 4px 12px rgba(232,55,42,0.08)}
        
        /* Светлые заглушки и фоны для изображений */
        .dish-card-img{width:100%;height:140px;object-fit:cover;display:block;background:var(--accent-soft, #f9f9f9);border-bottom:1px solid var(--accent-border, #f0f0f0)}
        .dish-card-img-placeholder{width:100%;height:140px;background:var(--accent-soft, #f9f9f9);display:flex;align-items:center;justify-content:center;font-size:2.5rem;color:#e8372a;border-bottom:1px solid var(--accent-border, #f0f0f0)}
        
        .dish-card-body{padding:12px;flex:1;display:flex;flex-direction:column;gap:6px}
        .dish-card-top{display:flex;justify-content:space-between;align-items:flex-start;gap:8px}
        .dish-card-top strong{font-size:.9rem;line-height:1.3;color:#222}
        .price-tag{font-size:.85rem;font-weight:700;color:#e8372a;white-space:nowrap}
        
        /* Читаемый темный текст описания для светлого фона */
        .dish-meta{font-size:.78rem;color:#666;flex:1;line-height:1.4;margin:0}
        
        /* Разделитель и элементы управления в светлых тонах */
        .qty-row{display:flex;align-items:center;gap:8px;margin-top:auto;padding-top:8px;border-top:1px solid var(--accent-border, #f0f0f0)}
        .qty-row input[type=number]{width:56px;text-align:center;border:1px solid var(--accent-border, #d1d1d1);border-radius:4px;padding:2px 4px}
        .qty-row input[type=checkbox]{width:18px;height:18px;accent-color:#e8372a;cursor:pointer;flex-shrink:0}
    </style>
</head>
<body>
<div class="panel-wrap">
    <header class="panel-header">
        <div class="panel-header-strip"></div>
        <div class="panel-header-inner">
            <div class="panel-brand">
                <h1 class="panel-title">NO_KFC</h1>
                <p class="panel-subtitle">Заказ еды на дом</p>
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

    <div id="flash" class="flash" style="display:none;" role="status"></div>

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

<script>
const token   = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const flashEl = document.getElementById('flash');

const statusRu = {
    new:'Новый', preparing:'Готовится', confirmed:'Готов к выдаче',
    on_the_way:'В пути', delivered:'Доставлен', cancelled:'Отменён'
};

function showFlash(text,isError=false){flashEl.style.display='block';flashEl.textContent=text;flashEl.classList.toggle('is-error',isError)}
function esc(s){return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;')}
function badgeClass(s){return s==='delivered'?'badge badge-ok':s==='cancelled'?'badge badge-warn':'badge'}

async function api(url,method='GET',body=null){
    const r=await fetch(url,{method,headers:{'Content-Type':'application/json','X-CSRF-TOKEN':token,'Accept':'application/json'},body:body?JSON.stringify(body):null});
    if(!r.ok){const e=await r.json().catch(()=>({}));throw new Error(e.message||(e.errors?Object.values(e.errors).flat().join(' '):'Ошибка запроса'))}
    if(r.status===204)return null;
    return r.json();
}

/* ── Меню ── */
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

/* ── Заказы ── */
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

/* ── Отмена ── */
async function cancelOrder(id,btn){
    if(!confirm('Отменить заказ № '+id+'?'))return;
    btn.disabled=true;btn.textContent='Отмена…';
    try{await api(`/app-api/customer/orders/${id}/cancel`,'PATCH');showFlash('Заказ № '+id+' отменён.',false);await loadOrders()}
    catch(e){showFlash(e.message,true);btn.disabled=false;btn.textContent='Отменить'}
}

/* ── Новый заказ ── */
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

loadDishes().then(loadOrders);
</script>
</body>
</html>