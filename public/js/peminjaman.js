const form = document.getElementById('formPeminjaman');

if (form) {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const tableBody = document.getElementById('dataPeminjaman');
    const message = document.getElementById('pesan');
    const idField = document.getElementById('peminjaman_id');
    const fileField = document.getElementById('bukti_peminjaman');
    const currentFile = document.getElementById('currentFile');
    const submitButton = document.getElementById('submitButton');
    const cancelEditButton = document.getElementById('cancelEdit');
    const showBorrower = form.dataset.showBorrower === 'true';
    const updateTemplate = form.dataset.updateUrl;
    const deleteTemplate = form.dataset.deleteUrl;

    function showMessage(text, isError = false) {
        message.textContent = text;
        message.classList.toggle('alert-error', isError);
        message.hidden = !text;
    }

    function renderErrors(result) {
        const messages = result.errors
            ? Object.values(result.errors).flat()
            : [result.message || 'Terjadi kesalahan. Silakan coba lagi.'];
        showMessage(messages.join(' '), true);
    }

    function addCell(row, value) {
        const cell = document.createElement('td');
        cell.textContent = value || '-';
        row.appendChild(cell);
        return cell;
    }

    function renderRows(items) {
        tableBody.replaceChildren();

        if (items.length === 0) {
            const row = document.createElement('tr');
            const cell = document.createElement('td');
            cell.colSpan = showBorrower ? 7 : 6;
            cell.innerHTML = '<div class="empty-state"><strong>Belum ada peminjaman</strong>Tambahkan peminjaman baru melalui formulir di atas.</div>';
            row.appendChild(cell);
            tableBody.appendChild(row);
            return;
        }

        items.forEach((item) => {
            const row = document.createElement('tr');

            if (showBorrower) {
                addCell(row, item.user?.name);
            }

            addCell(row, item.buku?.judul);
            addCell(row, item.tanggal_pinjam);
            addCell(row, item.tanggal_kembali);

            const statusCell = document.createElement('td');
            const status = document.createElement('span');
            status.className = `badge ${item.status === 'Dipinjam' ? 'badge-warning' : ''}`;
            status.textContent = item.status;
            statusCell.appendChild(status);
            row.appendChild(statusCell);

            const fileCell = document.createElement('td');
            if (item.bukti_url) {
                const link = document.createElement('a');
                link.href = item.bukti_url;
                link.target = '_blank';
                link.rel = 'noopener noreferrer';
                link.textContent = 'Lihat bukti';
                fileCell.appendChild(link);
            } else {
                fileCell.textContent = '-';
            }
            row.appendChild(fileCell);

            const actions = document.createElement('td');
            const actionGroup = document.createElement('div');
            actionGroup.className = 'table-actions';

            const editButton = document.createElement('button');
            editButton.type = 'button';
            editButton.className = 'button button-secondary button-small';
            editButton.textContent = 'Edit';
            editButton.addEventListener('click', () => beginEdit(item));
            actionGroup.appendChild(editButton);

            const deleteButton = document.createElement('button');
            deleteButton.type = 'button';
            deleteButton.className = 'button button-danger button-small';
            deleteButton.textContent = 'Hapus';
            deleteButton.addEventListener('click', () => deleteItem(item.id));
            actionGroup.appendChild(deleteButton);

            actions.appendChild(actionGroup);
            row.appendChild(actions);
            tableBody.appendChild(row);
        });
    }

    async function loadData() {
        try {
            const response = await fetch(form.dataset.listUrl, {
                headers: { Accept: 'application/json' },
            });
            const items = await response.json();

            if (!response.ok) {
                throw new Error(items.message || 'Data peminjaman gagal dimuat.');
            }

            renderRows(items);
        } catch (error) {
            showMessage(error.message || 'Data peminjaman gagal dimuat.', true);
        }
    }

    function beginEdit(item) {
        idField.value = item.id;
        form.elements.buku_id.value = item.buku_id;
        form.elements.tanggal_pinjam.value = item.tanggal_pinjam;
        form.elements.tanggal_kembali.value = item.tanggal_kembali || '';
        form.elements.status.value = item.status;
        fileField.value = '';
        currentFile.textContent = item.bukti_url ? 'Bukti saat ini tersedia. Unggah file baru untuk menggantinya.' : 'Belum ada bukti yang diunggah.';
        currentFile.hidden = false;
        submitButton.textContent = 'Simpan perubahan';
        cancelEditButton.hidden = false;
        document.getElementById('formTitle').textContent = 'Edit peminjaman';
        showMessage('');
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function resetForm() {
        form.reset();
        idField.value = '';
        currentFile.hidden = true;
        submitButton.textContent = 'Simpan peminjaman';
        cancelEditButton.hidden = true;
        document.getElementById('formTitle').textContent = 'Catat peminjaman';
    }

    async function deleteItem(id) {
        if (!window.confirm('Hapus catatan peminjaman ini?')) {
            return;
        }

        try {
            const response = await fetch(deleteTemplate.replace('__ID__', id), {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
            });
            const result = await response.json();

            if (!response.ok) {
                renderErrors(result);
                return;
            }

            showMessage(result.message);
            await loadData();
        } catch (error) {
            showMessage(error.message || 'Peminjaman gagal dihapus.', true);
        }
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        showMessage('');

        const id = idField.value;
        const formData = new FormData(form);
        const url = id
            ? updateTemplate.replace('__ID__', id)
            : form.dataset.storeUrl;

        if (id) {
            formData.set('_method', 'PUT');
        }

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: formData,
            });
            const result = await response.json();

            if (!response.ok) {
                renderErrors(result);
                return;
            }

            showMessage(result.message);
            resetForm();
            await loadData();
        } catch (error) {
            showMessage(error.message || 'Peminjaman gagal disimpan.', true);
        }
    });

    cancelEditButton.addEventListener('click', () => {
        resetForm();
        showMessage('');
    });

    loadData();
}
