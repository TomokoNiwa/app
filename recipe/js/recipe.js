/**
 * recipe.js - レシピ
 */
    let recipes = JSON.parse(localStorage.getItem('myRecipes') || '[]');
    let masterItems = JSON.parse(localStorage.getItem('myMaster') || '{}');
    let selectedTags = new Set();

    window.onload = () => { renderPalette(); update(); updateDatalist(); };

    function toggleMenu() { document.getElementById('headerTools').classList.toggle('open'); }

    function switchMode(mode) {
        document.getElementById('viewPage').classList.toggle('active', mode === 'view');
        document.getElementById('buildPage').classList.toggle('active', mode === 'build');
        document.getElementById('view-tab').classList.toggle('active', mode === 'view');
        document.getElementById('build-tab').classList.toggle('active', mode === 'build');
        document.getElementById('headerTools').classList.remove('open');
    }

    function update() {
        const kw = document.getElementById('kwInput').value.toLowerCase();
        const filtered = recipes.filter(r => {
            const mK = !kw || r.title.includes(kw) || r.ingredients.some(i => i.name.includes(kw));
            const mT = selectedTags.size === 0 || Array.from(selectedTags).every(t => r.ingredients.some(i => i.name.includes(t)));
            return mK && mT;
        });
        document.getElementById('grid').innerHTML = filtered.map((r, idx) => `
            <div class="recipe-card" onclick="openModal(${recipes.indexOf(r)})">
                <div class="recipe-img-box">${r.img ? `<img src="${r.img}">` : `<span style="font-size:1.8rem;">🍳</span>`}</div>
                <div class="recipe-info">${r.title}</div>
            </div>`).join('');
    }

    function openModal(index) {
        const r = recipes[index];
        const mains = r.ingredients.filter(i => i.type === 'main');
        const subs = r.ingredients.filter(i => i.type === 'sub');

        document.getElementById('modalContent').innerHTML = `
            <div style="padding:20px;">
                <h2 style="color:var(--main-color); margin-top:0; margin-bottom:10px;">${r.title}</h2>
                
                <div style="width:100%; max-height:250px; overflow:hidden; border-radius:15px; margin-bottom:15px; background:#fdf2f2; display:flex; align-items:center; justify-content:center;">
                    ${r.img ? `<img src="${r.img}" style="width:100%; height:auto; object-fit:cover;">` : `<span style="font-size:3rem; padding:20px;">🍳</span>`}
                </div>

                <p style="font-weight:bold; margin-bottom:10px;">2人前</p>
                
                <div style="margin-bottom:15px;">
                    <strong>🛒 材料</strong>
                    <ul style="padding-left:20px; margin-top:5px;">${mains.length ? mains.map(i=>`<li>${i.name}: ${i.amount}</li>`).join('') : '<li>なし</li>'}</ul>
                </div>
                <div style="margin-bottom:20px;">
                    <strong>🧂 調味料</strong>
                    <ul style="padding-left:20px; margin-top:5px;">${subs.length ? subs.map(i=>`<li>${i.name}: ${i.amount}</li>`).join('') : '<li>なし</li>'}</ul>
                </div>
                ${r.memo ? `<div style="background:#fff9db; padding:10px; border-radius:10px; margin-bottom:15px; font-size:0.9rem; white-space:pre-wrap;">${r.memo}</div>` : ''}
                
                ${r.url ? `<a href="${r.url}" target="_blank" style="display:block; text-align:center; margin-bottom:15px; color:var(--accent); text-decoration:none; font-weight:bold;">🔗 参考サイトを見る</a>` : ''}

                <button class="btn-primary" onclick="closeModal()">閉じる</button>
                <button class="btn-delete" onclick="deleteRecipe(${index})">🗑️ このレシピを削除する</button>
            </div>`;
        document.getElementById('modal').style.display = 'flex';
    }

    function deleteRecipe(index) {
        if(confirm("本当に削除してもよろしいですか？")) {
            recipes.splice(index, 1);
            localStorage.setItem('myRecipes', JSON.stringify(recipes));
            closeModal(); update();
        }
    }

    function saveRecipe() {
        const title = document.getElementById('recipeName').value;
        if(!title) return alert("料理名を入れてね");
        const names = document.querySelectorAll('.b-ing-name');
        const amounts = document.querySelectorAll('.b-ing-amount');
        const types = document.querySelectorAll('.b-ing-type');
        const ings = [];
        names.forEach((n, i) => { if(n.value) ings.push({name: n.value, amount: amounts[i].value, type: types[i].value}); });
        recipes.push({ title, img: document.getElementById('recipeImg').value, url: document.getElementById('recipeUrl').value, ingredients: ings, memo: document.getElementById('recipeMemo').value });
        localStorage.setItem('myRecipes', JSON.stringify(recipes));
        location.reload();
    }

    function addBuilderRow() {
        const div = document.createElement('div'); div.style = "display:flex; gap:5px; margin-bottom:8px;";
        div.innerHTML = `<select style="width:80px; margin:0;" class="b-ing-type"><option value="main">食材</option><option value="sub">調味料</option></select><input type="text" style="margin:0;" class="b-ing-name" placeholder="材料" list="masterList"><input type="text" style="margin:0;" class="b-ing-amount" placeholder="分量">`;
        document.getElementById('builderIngredientList').appendChild(div);
    }

    function closeModal() { document.getElementById('modal').style.display = 'none'; }
    function renderPalette() { 
        const p = document.getElementById('palette'); p.innerHTML = '';
        for (const [cat, items] of Object.entries(masterItems)) {
            const div = document.createElement('div');
            div.innerHTML = `<div class="cat-title">${cat}</div><div class="ing-tags"></div>`;
            items.forEach(item => {
                const span = document.createElement('span'); span.className = `ing-tag ${selectedTags.has(item) ? 'selected' : ''}`;
                span.textContent = item; span.onclick = () => { selectedTags.has(item) ? selectedTags.delete(item) : selectedTags.add(item); renderPalette(); update(); };
                div.querySelector('.ing-tags').appendChild(span);
            });
            p.appendChild(div);
        }
    }
    function handleImport(input) {
        const reader = new FileReader();
        reader.onload = () => {
            try {
                const data = JSON.parse(reader.result);
                if(Array.isArray(data)) recipes = data; else masterItems = data;
                localStorage.setItem('myRecipes', JSON.stringify(recipes));
                localStorage.setItem('myMaster', JSON.stringify(masterItems));
                location.reload();
            } catch(e) { alert("形式が正しくありません"); }
        };
        reader.readAsText(input.files[0]);
    }
    function updateDatalist() {
        document.getElementById('masterList').innerHTML = Object.values(masterItems).flat().map(i => `<option value="${i}">`).join('');
    }