(() => {
    const existing = document.getElementById('existing-client');
    const newClient = document.getElementById('new-client');
    const modality = document.getElementById('modalidad');
    const address = document.getElementById('delivery-address');
    const addressInput = document.getElementById('direccion_entrega');
    const details = document.getElementById('details');
    const updateClient = () => {
        const isNew = document.querySelector('input[name="cliente_modo"]:checked')?.value === 'nuevo';
        existing.classList.toggle('hidden', isNew); newClient.classList.toggle('hidden', !isNew);
        document.getElementById('id_cliente').required = !isNew;
        document.getElementById('cliente_nombres').required = isNew;
        document.getElementById('cliente_telefono').required = isNew;
    };
    const updateDelivery = () => { const delivery = modality.value === 'DELIVERY'; address.classList.toggle('hidden', !delivery); addressInput.required = delivery; };
    const renumber = () => details.querySelectorAll('.detail-row').forEach((row, index) => { row.querySelector('.detail-number').textContent = index + 1; row.querySelector('.remove-detail').disabled = details.children.length === 1; });
    document.querySelectorAll('input[name="cliente_modo"]').forEach(input => input.addEventListener('change', updateClient));
    modality.addEventListener('change', updateDelivery);
    document.getElementById('add-detail').addEventListener('click', () => {
        if (details.children.length >= 20) return;
        const row = details.firstElementChild.cloneNode(true);
        row.querySelectorAll('select').forEach(field => field.selectedIndex = 0);
        row.querySelectorAll('input').forEach(field => field.value = field.type === 'number' ? '1' : '');
        details.appendChild(row); renumber();
    });
    details.addEventListener('click', event => { if (!event.target.classList.contains('remove-detail') || details.children.length === 1) return; event.target.closest('.detail-row').remove(); renumber(); });
    updateClient(); updateDelivery(); renumber();
})();
