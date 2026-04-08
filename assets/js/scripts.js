// assets/js/scripts.js - Scripts do sistema

// MÃ¡scara para telefone
function maskTelefone(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length <= 11) {
        value = value.replace(/(\d{2})(\d)/, '($1) $2');
        value = value.replace(/(\d{4,5})(\d{4})$/, '$1-$2');
    }
    input.value = value;
}

// Inicializar primeiro produto
document.addEventListener('DOMContentLoaded', function() {
    const telefoneInputs = document.querySelectorAll('.mask-telefone');
    telefoneInputs.forEach(input => {
        input.addEventListener('input', function() {
            maskTelefone(this);
        });
    });

    // Adicionar primeiro produto se estiver na pÃ¡gina de pedidos
    if (document.getElementById('produtos-container')) {
        addProduto();
        
        // Filtrar produtos quando categoria mudar
        document.getElementById('categoria').addEventListener('change', function() {
            const categoria = this.value;
            if (categoria) {
                // Limpar produtos existentes e recarregar
                document.getElementById('produtos-container').innerHTML = '';
                produtoIndex = 0;
                addProduto();
            }
        });
    }
});

// Modal para produtos
function showForm() {
    document.getElementById('produtoModal').style.display = 'block';
    document.getElementById('modalTitle').textContent = 'Novo Produto';
    document.getElementById('action').value = 'create';
    document.getElementById('produtoId').value = '';
    document.getElementById('produtoForm').reset();
}

function closeModal() {
    document.getElementById('produtoModal').style.display = 'none';
}

function showCategoryForm() {
    const modal = document.getElementById('categoriaModal');
    if (modal) {
        modal.style.display = 'block';
    }
}

function closeCategoryModal() {
    const modal = document.getElementById('categoriaModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function editProduto(id) {
    // Aqui vocÃª pode fazer uma requisiÃ§Ã£o AJAX para buscar os dados do produto
    // Por simplicidade, vamos abrir o modal vazio e o usuário edita
    showForm();
    document.getElementById('modalTitle').textContent = 'Editar Produto';
    document.getElementById('action').value = 'update';
    document.getElementById('produtoId').value = id;
    // Preencher campos - precisaria de AJAX para buscar dados
}

// Produtos no pedido
let produtoIndex = 1;

function addProduto() {
    const container = document.getElementById('produtos-container');
    const produtoItem = document.createElement('div');
    produtoItem.className = 'produto-item';
    produtoItem.innerHTML = `
        <select name="produtos[${produtoIndex}][id]" class="produto-select" required>
            <option value="">Carregando...</option>
        </select>
        <input type="number" name="produtos[${produtoIndex}][quantidade]" class="quantidade" min="1" value="1" required>
        <button type="button" class="btn btn-danger remove-produto" style="padding: 8px 16px; font-size: 14px;">Remover</button>
    `;
    container.appendChild(produtoItem);

    // Carregar produtos via AJAX
    const categoria = document.getElementById('categoria').value;
    const xhr = new XMLHttpRequest();
    xhr.open('GET', '../actions/get_produtos.php?categoria=' + encodeURIComponent(categoria), true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            const produtos = JSON.parse(xhr.responseText);
            const select = produtoItem.querySelector('.produto-select');
            select.innerHTML = '<option value="">Selecione um produto</option>';
            produtos.forEach(produto => {
                const option = document.createElement('option');
                option.value = produto.id;
                option.setAttribute('data-preco', produto.preco);
                option.textContent = `${produto.nome} - ${produto.tamanho || '-'} - R$ ${parseFloat(produto.preco).toFixed(2).replace('.', ',')}`;
                select.appendChild(option);
            });
            updateTotal();
        }
    };
    xhr.send();

    produtoIndex++;
}

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-produto')) {
        e.target.parentElement.remove();
        updateTotal();
    }
});

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('produto-select') || e.target.classList.contains('quantidade')) {
        updateTotal();
    }
});

function updateTotal() {
    let total = 0;
    const produtoItems = document.querySelectorAll('.produto-item');
    produtoItems.forEach(item => {
        const select = item.querySelector('.produto-select');
        const quantidade = item.querySelector('.quantidade');
        if (select.value && quantidade.value) {
            const option = select.options[select.selectedIndex];
            const preco = parseFloat(option.getAttribute('data-preco'));
            total += preco * parseInt(quantidade.value);
        }
    });
    document.getElementById('total').textContent = total.toFixed(2).replace('.', ',');
}

// ConfirmaÃ§Ã£o de exclusÃ£o
function deleteProduto(id) {
    if (confirm('Tem certeza que deseja excluir este produto?')) {
        const form = document.createElement('form');
        form.method = 'post';
        form.action = '../actions/produto_crud.php';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function deletePedido(id) {
    if (confirm('Tem certeza que deseja excluir este pedido?')) {
        const form = document.createElement('form');
        form.method = 'post';
        form.action = '../actions/pedido_crud.php';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function viewDetails(id) {
    window.location.href = `detalhes_pedido.php?id=${id}`;
}

// Sidebar toggle
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    
    if (sidebar.classList.contains('open')) {
        sidebar.classList.remove('open');
        overlay.classList.remove('show');
    } else {
        sidebar.classList.add('open');
        overlay.classList.add('show');
    }
}

function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    sidebar.classList.remove('open');
    overlay.classList.remove('show');
}

// Fechar sidebar ao clicar no overlay
document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('sidebar-overlay');
    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }
});

